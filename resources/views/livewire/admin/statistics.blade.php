<?php

namespace App\Livewire;

use App\Models\Guild;
use App\Models\GuildSelector;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title};
use Carbon\Carbon;

new
#[Layout('layouts.app')]
#[Title('Szolgálati Statisztikák')]
class extends Component {
    public ?Guild $guild = null;
    public array $dutyHoursData = [];

    public array $area_data = [];

    public function mount()
    {
        $this->guild = GuildSelector::getGuild();

        $this->getDiagramData();
    }

    protected function getData(int $days = 30): Collection
    {
        return $this->guild->dutiesWithTrashed()
            ->selectRaw('DATE(created_at) as day, SUM(value) as total_minutes, COUNT(DISTINCT user_discord_id) as unique_users')
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->whereNotNull('value')
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->get();
    }

    protected function getDiagramData(): void
    {
        $data = $this->getData();

        $this->area_data = [];

        foreach ($data as $item) {
            $this->area_data[] = [
                'day' => Carbon::parse($item->day)->format('M d'),
                'minutes' => (int) $item->total_minutes,
                'users' => (int) $item->unique_users,
            ];
        }
    }
};
?>

<div>
    <h2 class="text-xl font-semibold mb-4">Duty idő eloszlás az elmúlt 30 napban</h2>
    <div class="grid grid-cols-2 gap-4">
        <div id="dutyAreaChart" class="col-span-2 h-[400px]" wire:ignore></div>
    </div>

    @script
    <script>
        let chart;

        // Sötét mód detektálása (figyeli mind a rendszer beállítást, mind a Tailwind dark osztályt)
        function isDarkMode() {
            return document.documentElement.classList.contains('dark') ||
                window.matchMedia('(prefers-color-scheme: dark)').matches;
        }

        // Diagram színeinek beállítása
        function getChartTheme() {
            const dark = isDarkMode();
            return {
                textColor: dark ? '#000' : '#fff',
                axisColor: dark ? '#6B7280' : '#9CA3AF',
                bgColor: 'transparent',
                seriesColors: dark ? ['#3B82F6', '#10B981'] : ['#000000', '#6B7280'],
                tooltipText: dark ? 'text-gray-100' : 'text-white'
            };
        }

        // Diagram opciók
        function getChartOptions(dutyData) {
            const filteredData = dutyData.filter(item => item.minutes > 0);
            if (filteredData.length === 0) return null;

            const labels = filteredData.map(item => item.day);
            const minutesSeries = filteredData.map(item => item.minutes);
            const userSeries = filteredData.map(item => item.users);

            const theme = getChartTheme();

            return {
                chart: {
                    type: 'area',
                    height: Math.min(400, window.innerHeight - 200),
                    width: Math.min(1360, window.innerWidth - 50),
                    zoom: { enabled: true },
                    toolbar: { show: true },
                    foreColor: theme.textColor,
                    background: theme.bgColor,
                },
                series: [
                    {
                        name: 'Duty idő (perc)',
                        data: minutesSeries
                    },
                    {
                        name: 'Felhasználók száma',
                        data: userSeries
                    }
                ],
                xaxis: {
                    categories: labels,
                    labels: {
                        style: {
                            fontSize: '12px',
                            colors: theme.textColor
                        }
                    },
                    axisBorder: {
                        color: theme.axisColor
                    },
                    axisTicks: {
                        color: theme.axisColor
                    }
                },
                yaxis: [
                    {
                        title: {
                            text: 'Duty idő (perc)',
                            style: {
                                color: theme.textColor
                            }
                        },
                        labels: {
                            formatter: val => {
                                const hours = Math.floor(val / 60);
                                const minutes = val % 60;
                                return `${hours}ó ${minutes}p`;
                            },
                            style: {
                                colors: theme.textColor
                            }
                        }
                    },
                    {
                        title: {
                            text: 'Felhasználók száma',
                            style: {
                                color: theme.textColor
                            }
                        },
                        opposite: true,
                        labels: {
                            style: {
                                colors: theme.textColor
                            }
                        }
                    }
                ],
                tooltip: {
                    theme: isDarkMode() ? 'dark' : 'light',
                    shared: true,
                    custom: function({ series, dataPointIndex, w }) {
                        const label = labels[dataPointIndex];
                        const minutes = series[0][dataPointIndex];
                        const users = series[1][dataPointIndex];
                        const hours = Math.floor(minutes / 60);
                        const mins = minutes % 60;
                        const theme = getChartTheme();

                        return `
                            <div class="p-2 ${theme.tooltipBg} ${theme.tooltipText} border-b border-gray-600">
                                ${label}
                            </div>
                            <div class="p-2 ${theme.tooltipBg} ${theme.tooltipText}">
                                ⏱️ ${hours} óra ${mins} perc<br>
                                👥 ${users} felhasználó
                            </div>
                        `;
                    }
                },
                fill: {
                    type: 'gradient',
                    gradient: {
                        stops: [0, 90, 100],
                        gradientToColors: theme.seriesColors
                    }
                },
                colors: theme.seriesColors,
                stroke: {
                    curve: 'smooth',
                    width: 3,
                    colors: theme.seriesColors
                },
                grid: {
                    borderColor: theme.gridColor,
                    row: {
                        colors: ['transparent'],
                        opacity: 0.2
                    }
                },
                dataLabels: {
                    enabled: false
                },
                legend: {
                    labels: {
                        colors: theme.textColor
                    }
                }
            };
        }

        // Téma frissítése
        function updateChartTheme() {
            if (chart) {
                const options = getChartOptions(@json($area_data));
                if (options) {
                    chart.updateOptions(options);
                }
            }
        }

        // Diagram renderelése
        function renderChart(dutyData) {
            if (chart) chart.destroy();

            const options = getChartOptions(dutyData);
            if (!options) {
                document.getElementById('dutyAreaChart').innerHTML =
                    '<div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">Nincs megjeleníthető adat</div>';
                return;
            }

            chart = new ApexCharts(document.querySelector("#dutyAreaChart"), options);
            chart.render();
        }

        // Sötét mód változás figyelése
        const darkModeObserver = new MutationObserver(() => {
            updateChartTheme();
        });

        darkModeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });

        // Kezdeti renderelés
        renderChart(@json($area_data));

        // Ablak átméretezés kezelése
        window.addEventListener('resize', function() {
            if (chart) {
                chart.updateOptions({
                    chart: {
                        height: Math.min(400, window.innerHeight - 200),
                        width: Math.min(1360, window.innerWidth - 50)
                    }
                }, false, false);
            }
        });
    </script>
    @endscript
</div>
