<?php

use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\Guild;
use App\Models\GuildSelector;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\{Layout, Title, On};
use TallStackUi\Traits\Interactions;

new
#[Layout('layouts.app')]
#[Title('Vizsgakezelő')]
class extends Component {

    use Interactions;

    public ?Exam $selected_exam = null;

    public ?Collection $questions = null;

    public array $user_answers = [];

    public ?ExamQuestion $selected_question = null;

    public int $current_question_index = 0;

    public int $question_count = 0;

    public ?Guild $guild = null;

    #[On('selectExam')]
    public function selectExam(Exam $exam): void
    {
        if (auth()->user()->exam_results()->count() >= $exam->attempt_count) {
            $this->toast()->error('Elérted a maximum próbálkozási lehetőséget.')->send();
            return;
        }

        $user = auth()->user();
        $guild = $this->guild;

        $member_data = Cache::remember("member_data_{$guild->guild_id}_{$user->discord_id}", now()->addMinutes(5), function () use ($guild, $user) {
            return getMemberData($this->guild->guild_id, $user->discord_id);
        });

        $member_roles = $member_data['roles'] ?? [];

        $whitelist = $exam->role_whitelist;
        if (is_string($whitelist)) {
            $whitelist = json_decode($whitelist, true);
        }

        if (!collect($member_roles)->intersect($whitelist)->isNotEmpty()) {
            $this->toast()->error('Ehhez a vizsgához nem rendelkezel megfelelő Discord szerepkörrel.')->send();
            return;
        }

        if (auth()->user())

            $this->selected_exam = $exam;

        $this->selected_exam->results()->create([
            'user_discord_id' => auth()->id(),
            'score' => 0,
        ]);

        $this->questions = $this->getQuestionsWithAnswers();

        $this->question_count = count($this->questions);

        $this->current_question_index = 0;

        $this->nextQuestion();
    }

    public function resetSelectExam(): void
    {
        $this->user_answers = [];
        $this->current_question_index = 0;
        $this->selected_question = null;
        $this->questions = null;
        $this->question_count = 0;

        $this->dispatch('examReset');

        $this->selected_exam = null;

    }

    public function getQuestionsWithAnswers(): ?Collection
    {
        return $this->selected_exam->questions()->with('answers')->inRandomOrder()->take($this->selected_exam->q_number)->get();;
    }

    #[On('timeUp')]
    public function timeUp(): void
    {
        $this->nextQuestion();
    }

    public function mount(): void
    {
        $this->guild = GuildSelector::getGuild();
    }


    public function nextQuestion(): void
    {
        if ($this->selected_question !== null) {
            $this->current_question_index++;
        }

        if ($this->current_question_index < $this->question_count) {
            $this->selected_question = $this->questions[$this->current_question_index];

            $this->dispatch('questionChanged', $this->selected_exam->minute_per_task);
        } else {
            $this->completeExam();
            $this->current_question_index = 0;
        }
    }

    private function calculateScore(): int
    {
        $score = 0;

        foreach ($this->questions as $question_index => $question_data) {

            $correct_answer_indexes = [];

            foreach ($question_data->answers as $answer_index => $answer_data) {
                if ($answer_data->correct)
                    $correct_answer_indexes[] = $answer_index;
            }

            if ($correct_answer_indexes === array_keys($this->user_answers[$question_index])) {
                $score += 1;
            }
        }

        return $score ?? 0;
    }

    private function completeExam(): void
    {
        $score = $this->calculateScore();

        $this->selected_exam->results()->where('user_discord_id', auth()->id())->latest()->first()->update([
            'score' => $score,
            'passed' => $this->selected_exam->min_pass_score <= $score,
        ]);

        if ($this->selected_exam->min_pass_score <= $score)
            $this->toast()->success('A vizsgát sikerült teljesítened. Eredményed ' . $score . ' pont.')->send();
        else
            $this->toast()->error('A vizsgát nem sikerült teljesítened. Eredményed ' . $score . ' pont.')->send();

        $this->resetSelectExam();
    }

    public function getCurrentQuestionNumber(): int
    {
        return $this->current_question_index + 1;
    }

    public function isLastQuestion(): bool
    {
        return $this->current_question_index + 1 >= $this->question_count;
    }

}; ?>
<div>
    @empty($selected_exam)
        @livewire('exam.user-table')
    @endempty
    @isset($selected_exam)
        <x-card>
            <x-slot:header>
                <div class="flex flex-wrap gap-4">
                    <x-input readonly label="Vizsga neve" :value="$selected_exam->name"/>
                    <x-input readonly label="Ennyi percet tölthetsz egy feladattal"
                             :value="$selected_exam->minute_per_task"/>
                    <x-input readonly label="Sikeres vizsgához szükséges pontszám"
                             :value="$selected_exam->min_pass_score"/>
                    <x-input readonly label="Próbálkozási lehetőség" :value="$selected_exam->attempt_count"/>
                </div>
            </x-slot:header>
            @isset($selected_question)
                <div class="flex flex-col gap-4 mb-6">
                    <div class="text-lg mb-2">
                        Kérdés {{ $this->getCurrentQuestionNumber() }} / {{ $question_count }}
                        <span id="question-timer" class="text-red-500 font-bold float-right"></span>
                    </div>

                    <x-textarea :value="$selected_question->question" readonly/>

                    <div class="flex flex-wrap gap-4">
                        @foreach($selected_question->answers as $answer_index => $answer_data)
                            <div class="px-4 py-2 border border-indigo-500">
                                <x-checkbox
                                    label="{{ $answer_data->answer }}"
                                    wire:model="user_answers.{{ $current_question_index }}.{{ $answer_index }}"
                                />
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end">
                    @if(!$this->isLastQuestion())
                        <x-button wire:click="nextQuestion" wire:loading.attr="disabled">
                            <span wire:loading.remove>Következő</span>
                            <span wire:loading>Betöltés...</span>
                        </x-button>
                    @else
                        <x-button wire:click="nextQuestion" wire:loading.attr="disabled">
                            <span wire:loading.remove>Befejezés</span>
                            <span wire:loading>Befejezés...</span>
                        </x-button>
                    @endif
                </div>
            @endisset
        </x-card>
    @endisset
</div>
@script
<script>
    let examTimer = null;
    let timeLeft = 0;

    function startQuestionTimer(minutes) {
        if (examTimer) {
            clearInterval(examTimer);
        }

        timeLeft = minutes * 60;

        examTimer = setInterval(() => {
            timeLeft--;

            updateTimerDisplay();

            if (timeLeft <= 0) {
                clearInterval(examTimer);
                // Livewire nextQuestion metódus hívása
                Livewire.dispatch('timeUp');
            }
        }, 1000);
    }

    function stopQuestionTimer() {
        if (examTimer) {
            clearInterval(examTimer);
            examTimer = null;
        }
    }

    function updateTimerDisplay() {
        const minutes = Math.floor(timeLeft / 60);
        const seconds = timeLeft % 60;
        const display = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;

        const timerElement = document.getElementById('question-timer');
        if (timerElement) {
            timerElement.textContent = display;
        }
    }

    document.addEventListener('livewire:initialized', () => {
        Livewire.on('questionChanged', (data) => {
            const minutesPerTask = data[0] || 1;
            startQuestionTimer(minutesPerTask);
        });

        Livewire.on('examReset', () => {
            stopQuestionTimer();
        });
    });

    window.addEventListener('beforeunload', () => {
        stopQuestionTimer();
    });
</script>
@endscript
