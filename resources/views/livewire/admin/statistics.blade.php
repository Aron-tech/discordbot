<?php

namespace App\Livewire;

use App\Enums\Guild\SettingTypeEnum;
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
    public array $user_activity_data = [];
    public array $duty_distribution_data = [];

    public function mount(): void
    {
        $this->guild = GuildSelector::getGuild();

        $this->getDiagramData();
        $this->getUserActivityData();
        $this->getDutyDistributionData();
    }

    protected function getData(int $days = 30): Collection
    {
        return dd( $this->guild->dutiesWithTrashed()
            ->selectRaw('DATE(created_at) as day, SUM(value) as total_minutes, COUNT(DISTINCT user_discord_id) as unique_users')
            ->whereNotNull('value')
            ->whereNotNull('end_time')
            ->where('end_time', '>=', Carbon::now()->subDays($days))
            ->groupBy('day')
            ->orderBy('day', 'asc')
            ->get());
    }

    protected function getActiveUsersCount(): ?int
    {
        return $this->guild->users()
            ->whereHas('duties', function ($query) {
                $query->where('created_at', '>=', Carbon::now()->subDays(getSettingValue($this->guild, SettingTypeEnum::WARN_TIME->value)))
                    ->where('guild_guild_id', $this->guild->guild_id);
            })->count();
    }

    protected function getInactiveUsersCount(): ?int
    {
        return $this->guild->users()
            ->whereDoesntHave('duties', function ($query) {
                $query->where('created_at', '>=', Carbon::now()->subDays(getSettingValue($this->guild, SettingTypeEnum::WARN_TIME->value)))
                    ->where('guild_guild_id', $this->guild->guild_id);
            })->count();
    }

    protected function getUsersWithDuties(): Collection
    {
        return $this->guild->users()->withSum('duties', 'value')
            ->whereHas('duties', function ($query) {
                $query->where('created_at', '>=', Carbon::now()->subDays(getSettingValue($this->guild, SettingTypeEnum::WARN_TIME->value)))
                    ->where('guild_guild_id', $this->guild->guild_id);
            })
            ->get();
    }

    protected function getDiagramData(): void
    {
        $data = $this->getData();

        $this->area_data = [];

        foreach ($data as $item) {
            $this->area_data[] = [
                'day' => Carbon::parse($item->day)->format('M d'),
                'minutes' => (int)$item->total_minutes,
                'users' => (int)$item->unique_users,
            ];
        }
    }

    protected function getUserActivityData(): void
    {
        $active = $this->getActiveUsersCount() ?? 0;
        $inactive = $this->getInactiveUsersCount() ?? 0;

        $this->user_activity_data = [
            ['label' => 'Aktív felhasználók', 'value' => $active],
            ['label' => 'Inaktív felhasználók', 'value' => $inactive]
        ];
    }

    protected function getDutyDistributionData(): void
    {
        $usersWithDuties = $this->getUsersWithDuties();

        $this->duty_distribution_data = [];

        foreach ($usersWithDuties as $user) {
            $totalMinutes = $user->duties_sum_value ?? 0;

            if ($totalMinutes > 0) {
                // Formázott idő számítása óra:perc formátumban
                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;
                $formattedTime = sprintf('%d:%02d', $hours, $minutes);

                $this->duty_distribution_data[] = [
                    'label' => ($user->name ?? 'Ismeretlen') . ' (' . $formattedTime . ')',
                    'value' => $totalMinutes, // Itt percekben tároljuk, hogy az arányok stimmeljenek
                    'user_name' => $user->name ?? 'Ismeretlen',
                    'formatted_time' => $formattedTime,
                    'minutes' => $totalMinutes
                ];
            }
        }

        // Rendezzük csökkenő sorrendbe a szolgálati idő alapján
        usort($this->duty_distribution_data, function($a, $b) {
            return $b['value'] - $a['value'];
        });
    }
};
?>

<div>
    <h2 class="text-xl font-semibold mb-4">Duty idő eloszlás az elmúlt 30 napban</h2>
    <div class="grid grid-cols-2 gap-4">
        <div id="dutyAreaChart" class="col-span-2 h-[400px]" wire:ignore></div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4 text-center text-gray-900 dark:text-white">Felhasználói Aktivitás</h3>
            <div id="userActivityChart" class="h-[300px]" wire:ignore></div>
        </div>

        <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow">
            <h3 class="text-lg font-semibold mb-4 text-center text-gray-900 dark:text-white">Szolgálati Idő Eloszlás ({{getSettingValue($guild, SettingTypeEnum::WARN_TIME->value)}} nap)</h3>
            <div id="dutyDistributionChart" class="h-[300px]" wire:ignore></div>
        </div>
    </div>

    @script
    <script>
        let areaChart;
        let activityChart;
        let distributionChart;

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
                tooltipText: dark ? 'text-gray-100' : 'text-white',
                donutColors: dark ? ['#3B82F6', '#EF4444', '#10B981', '#F59E0B', '#8B5CF6'] : ['#1E40AF', '#DC2626', '#059669', '#D97706', '#7C3AED']
            };
        }

        // Area diagram opciók
        function getAreaChartOptions(dutyData) {
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
                    zoom: {enabled: true},
                    toolbar: {show: true},
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
                    custom: function ({series, dataPointIndex}) {
                        const label = labels[dataPointIndex];
                        const minutes = series[0][dataPointIndex];
                        const users = series[1][dataPointIndex];
                        const hours = Math.floor(minutes / 60);
                        const mins = minutes % 60;

                        return `
                            <div class="p-2 border-b border-gray-600">
                                ${label}
                            </div>
                            <div class="p-2">
                                ${(typeof hours === 'number' || typeof mins === 'number')
                            ? `⏱️ ${hours ?? 0} óra ${mins ?? 0} perc<br>`
                            : ''
                        }
                                ${typeof users === 'number' ? `👥 ${users} felhasználó` : ''}
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

        // Donut diagram alapbeállítások
        function getDonutChartOptions(data, title) {
            const theme = getChartTheme();

            if (!data || data.length === 0) return null;

            return {
                chart: {
                    type: 'donut',
                    height: 300,
                    foreColor: theme.textColor,
                    background: 'transparent',
                },
                series: data.map(item => item.value),
                labels: data.map(item => item.label),
                colors: theme.donutColors,
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Összesen',
                                    fontSize: '16px',
                                    fontWeight: 600,
                                    color: theme.textColor,
                                    formatter: function (w) {
                                        return w.globals.seriesTotals.reduce((a, b) => {
                                            return a + b;
                                        }, 0);
                                    }
                                },
                                value: {
                                    show: true,
                                    fontSize: '24px',
                                    fontWeight: 'bold',
                                    color: theme.textColor,
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        return Math.round(val) + '%';
                    },
                    style: {
                        fontSize: '12px',
                        colors: ['#fff']
                    }
                },
                tooltip: {
                    theme: isDarkMode() ? 'dark' : 'light',
                    y: {
                        formatter: function(val) {
                            // Ellenőrizzük, hogy melyik diagram típusról van szó
                            if (title === 'Szolgálati Idő Eloszlás') {
                                const hours = Math.floor(val / 60);
                                const minutes = val % 60;
                                return `${hours}:${minutes.toString().padStart(2, '0')}`;
                            } else {
                                return val + ' fő';
                            }
                        }
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    labels: {
                        colors: theme.textColor
                    }
                },
                responsive: [{
                    breakpoint: 480,
                    options: {
                        chart: {
                            height: 250
                        },
                        legend: {
                            position: 'bottom'
                        }
                    }
                }]
            };
        }

        // Téma frissítése
        function updateChartThemes() {
            if (areaChart) {
                const areaOptions = getAreaChartOptions(@json($area_data));
                if (areaOptions) {
                    areaChart.updateOptions(areaOptions);
                }
            }

            if (activityChart) {
                const activityOptions = getDonutChartOptions(@json($user_activity_data), 'Felhasználói Aktivitás');
                if (activityOptions) {
                    activityChart.updateOptions(activityOptions);
                }
            }

            if (distributionChart) {
                const distributionOptions = getDonutChartOptions(@json($duty_distribution_data), 'Szolgálati Idő Eloszlás');
                if (distributionOptions) {
                    distributionChart.updateOptions(distributionOptions);
                }
            }
        }

        // Area diagram renderelése
        function renderAreaChart(dutyData) {
            if (areaChart) areaChart.destroy();

            const options = getAreaChartOptions(dutyData);
            if (!options) {
                document.getElementById('dutyAreaChart').innerHTML =
                    '<div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">Nincs megjeleníthető adat</div>';
                return;
            }

            areaChart = new ApexCharts(document.querySelector("#dutyAreaChart"), options);
            areaChart.render();
        }

        // Aktivitás diagram renderelése
        function renderActivityChart(data) {
            if (activityChart) activityChart.destroy();

            const options = getDonutChartOptions(data, 'Felhasználói Aktivitás');
            if (!options) {
                document.getElementById('userActivityChart').innerHTML =
                    '<div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">Nincs megjeleníthető adat</div>';
                return;
            }

            activityChart = new ApexCharts(document.querySelector("#userActivityChart"), options);
            activityChart.render();
        }
        function renderDistributionChart(data) {
            if (distributionChart) distributionChart.destroy();

            // Szűrjük ki a 0 értékű bejegyzéseket és rendezzük csökkenő sorrendbe
            const filteredData = data.filter(item => item.value > 0)
                .sort((a, b) => b.value - a.value);

            if (filteredData.length === 0) {
                document.getElementById('dutyDistributionChart').innerHTML =
                    '<div class="flex items-center justify-center h-full text-gray-500 dark:text-gray-400">Nincs megjeleníthető adat</div>';
                return;
            }

            const theme = getChartTheme();

            // Összes perc kiszámolása az arányokhoz
            const totalMinutes = filteredData.reduce((sum, item) => sum + item.value, 0);

            const options = {
                chart: {
                    type: 'donut',
                    height: 300,
                    foreColor: theme.textColor,
                    background: 'transparent',
                },
                series: filteredData.map(item => item.value),
                labels: filteredData.map(item => {
                    const percentage = ((item.value / totalMinutes) * 100).toFixed(1);
                    return `${item.user_name} (${percentage}%)`;
                }),
                colors: theme.donutColors,
                plotOptions: {
                    pie: {
                        donut: {
                            size: '60%',
                            labels: {
                                show: true,
                                total: {
                                    show: true,
                                    showAlways: true,
                                    label: 'Összes idő',
                                    fontSize: '16px',
                                    fontWeight: 600,
                                    color: theme.textColor,
                                    formatter: function () {
                                        const hours = Math.floor(totalMinutes / 60);
                                        const minutes = totalMinutes % 60;
                                        return `${hours}ó ${minutes}p`;
                                    }
                                },
                                value: {
                                    show: true,
                                    fontSize: '24px',
                                    fontWeight: 'bold',
                                    color: theme.textColor,
                                }
                            }
                        }
                    }
                },
                dataLabels: {
                    enabled: true,
                    formatter: function (val, opts) {
                        const minutes = filteredData[opts.seriesIndex].value;
                        const hours = Math.floor(minutes / 60);
                        const mins = minutes % 60;
                        return `${hours}:${mins.toString().padStart(2, '0')}`;
                    },
                    style: {
                        fontSize: '12px',
                        colors: ['#fff'],
                        fontFamily: 'monospace'
                    }
                },
                tooltip: {
                    theme: isDarkMode() ? 'dark' : 'light',
                    custom: function({series, seriesIndex}) {
                        const item = filteredData[seriesIndex];
                        const percentage = ((item.value / totalMinutes) * 100).toFixed(1);
                        return `
                            <div class="p-2">
                                <div class="font-bold">${item.user_name}</div>
                                <div>Idő: ${item.formatted_time}</div>
                                <div>Arány: ${percentage}%</div>
                            </div>
                        `;
                    }
                },
                legend: {
                    position: 'bottom',
                    horizontalAlign: 'center',
                    labels: {
                        colors: theme.textColor,
                        useSeriesColors: false
                    },
                    formatter: function(seriesName, opts) {
                        return seriesName.split(' (')[0];
                    }
                }
            };

            distributionChart = new ApexCharts(document.querySelector("#dutyDistributionChart"), options);
            distributionChart.render();
        }

        // Sötét mód változás figyelése
        const darkModeObserver = new MutationObserver(() => {
            updateChartThemes();
        });

        darkModeObserver.observe(document.documentElement, {
            attributes: true,
            attributeFilter: ['class']
        });

        // Kezdeti renderelés
        renderAreaChart(@json($area_data));
        renderActivityChart(@json($user_activity_data));
        renderDistributionChart(@json($duty_distribution_data));

        // Ablak átméretezés kezelése
        window.addEventListener('resize', function () {
            if (areaChart) {
                areaChart.updateOptions({
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
