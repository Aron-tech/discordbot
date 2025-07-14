<?php

use App\Models\Exam;
use App\Models\ExamAnswer;
use App\Models\ExamQuestion;
use App\Models\Guild;
use Illuminate\Support\Collection;
use Livewire\Volt\Component;
use Livewire\Attributes\{Layout, Title, On};
use Livewire\WithPagination;
use TallStackUi\Traits\Interactions;
use Illuminate\Support\Facades\Session;
use App\Models\GuildSelector;

new
#[Layout('layouts.app')]
#[Title('Vizsgakezelő')]
class extends Component {

    use Interactions;

    public ?Guild $guild = null;

    public ?Exam $selected_exam = null;
    public ?Collection $question_answer = null;
    public array $editing_data = [];

    public ?array $roles = [];

    public string $exam_name = '';
    public int $attempt_count = 1;
    public int $min_pass_score = 0;
    public int $minute_per_task = 1;
    public bool $exam_visible = false;
    public int $q_number = 0;
    public ?array $role_whitelist = [];

    public const EXAM_SESSION_KEY = 'selected_exam_id';

    #[On('selectExam')]
    public function selectExam($id)
    {
        Session::put(self::EXAM_SESSION_KEY, $id);
        $this->selected_exam = Exam::find($id);
        $this->exam_name = $this->selected_exam->name;
        $this->attempt_count = $this->selected_exam->attempt_count;
        $this->min_pass_score = $this->selected_exam->min_pass_score;
        $this->minute_per_task = $this->selected_exam->minute_per_task;
        $this->exam_visible = $this->selected_exam->visible;
        $this->q_number = $this->selected_exam->q_number;
        $this->role_whitelist = $this->selected_exam->role_whitelist;
        $this->question_answer = $this->getQuestionAndAnswer();
        $this->initializeEditingData();
    }

    public function mount()
    {
        $this->guild = GuildSelector::getGuild();

        $this->roles = $this->getRoles();

        if (Session::has(self::EXAM_SESSION_KEY)) {
            $this->selected_exam = Exam::find(Session::get(self::EXAM_SESSION_KEY));
            $this->exam_name = $this->selected_exam->name;
            $this->attempt_count = $this->selected_exam->attempt_count;
            $this->min_pass_score = $this->selected_exam->min_pass_score;
            $this->minute_per_task = $this->selected_exam->minute_per_task;
            $this->exam_visible = $this->selected_exam->visible;
            $this->q_number = $this->selected_exam->q_number ?? 0;
            $this->role_whitelist = $this->selected_exam->role_whitelist;
            $this->question_answer = $this->getQuestionAndAnswer();
            $this->initializeEditingData();
        }
    }

    public function getQuestionAndAnswer(): Collection
    {
        return $this->selected_exam->questions()
            ->with('answers')
            ->get();
    }

    public function initializeEditingData(): void
    {
        $this->editing_data = [];
        foreach ($this->question_answer as $question) {
            $this->editing_data[$question->id] = [
                'question' => $question->question,
                'answers' => []
            ];

            foreach ($question->answers as $answer) {
                $this->editing_data[$question->id]['answers'][] = [
                    'id' => $answer->id,
                    'answer' => $answer->answer,
                    'correct' => $answer->correct,
                    'is_new' => false
                ];
            }
        }
    }

    public function resetSelectedExam(): void
    {
        if (Session::has(self::EXAM_SESSION_KEY))
            Session::forget(self::EXAM_SESSION_KEY);
        $this->selected_exam = null;
        $this->exam_name = '';
        $this->attempt_count = 1;
        $this->minute_per_task = 1;
        $this->min_pass_score = 0;
        $this->exam_visible = false;
        $this->q_number = 0;
        $this->role_whitelist = [];
        $this->editing_data = [];

    }

    public function addNewAnswer($question_id): void
    {
        if (!isset($this->editing_data[$question_id])) {
            $this->editing_data[$question_id] = [
                'question' => '',
                'answers' => []
            ];
        }

        $this->editing_data[$question_id]['answers'][] = [
            'id' => 'null',
            'answer' => '',
            'correct' => false,
            'is_new' => true
        ];
    }

    public function addNewQuestion(): void
    {
        $temp_id = 'temp_' . uniqid();

        $this->editing_data[$temp_id] = [
            'question' => '',
            'answers' => [],
            'is_new' => true,
        ];
    }

    public function removeNewQuestion($question_id): void
    {
        if (isset($this->editing_data[$question_id])) {
            unset($this->editing_data[$question_id]);
            $this->toast()->success('Mentetlen kérdés sikeresen törlésre került!')->send();
        } else {
            $this->toast()->error('A kérdés törlése nem sikerült!')->send();
        }
    }

    public function deleteQuestion($question_id): void
    {
        $this->dialog()
            ->question('Figyelmeztetés!', 'Biztosan törölni szeretnéd a kérdést?')
            ->confirm('Törlés', 'destroyQuestion', $question_id)
            ->cancel('Mégse', 'cancelled', 'A kérdés törlésre visszavonásra került.')
            ->send();
    }

    public function destroyQuestion(ExamQuestion $question): void
    {
        $question->answers()->delete();
        $question->delete();
        $this->question_answer = $this->getQuestionAndAnswer();
        $this->initializeEditingData();

        $this->toast()->success('Kérdés sikeresen törlésre került!')->send();
    }

    public function removeAnswer($question_id, $answer_index): void
    {
        if (isset($this->editing_data[$question_id]['answers'][$answer_index])) {
            unset($this->editing_data[$question_id]['answers'][$answer_index]);
            $this->editing_data[$question_id]['answers'] = array_values($this->editing_data[$question_id]['answers']);
        }

        $this->toast()->success('Mentetlen válasz sikeresen törlésre került!')->send();
    }

    public function destroyAnswer(ExamAnswer $answer): void
    {
        $answer->delete();

        $this->question_answer = $this->getQuestionAndAnswer();
        $this->initializeEditingData();

        $this->toast()->success('Válasz sikeresen törlésre került!')->send();
    }

    public function saveQuestion($question_id): void
    {
        if (isset($this->editing_data[$question_id]['is_new']) && $this->editing_data[$question_id]['is_new']) {
            $question = ExamQuestion::create([
                'exam_id' => $this->selected_exam->id,
                'question' => $this->editing_data[$question_id]['question'],
            ]);

            foreach ($this->editing_data[$question_id]['answers'] as $answer_data) {
                ExamAnswer::create([
                    'exam_question_id' => $question->id,
                    'answer' => $answer_data['answer'],
                    'correct' => $answer_data['correct']
                ]);
            }
        } else {
            $question = ExamQuestion::find($question_id);
            if (!$question) return;

            $question->update([
                'question' => $this->editing_data[$question_id]['question'],
            ]);

            foreach ($this->editing_data[$question_id]['answers'] as $answer_data) {
                if ($answer_data['is_new']) {
                    ExamAnswer::create([
                        'exam_question_id' => $question->id,
                        'answer' => $answer_data['answer'],
                        'correct' => $answer_data['correct']
                    ]);
                } else {
                    $answer = ExamAnswer::find($answer_data['id']);
                    if ($answer) {
                        $answer->update([
                            'answer' => $answer_data['answer'],
                            'correct' => $answer_data['correct']
                        ]);
                    }
                }
            }
        }

        $this->question_answer = $this->getQuestionAndAnswer();
        $this->initializeEditingData();

        $this->toast()->success('Kérdés és válaszok sikeresen mentve!')->send();
    }

    public function cancelled(string $message): void
    {
        $this->toast()->info($message)->send();
    }

    public function deleteAnswer($answer_id, $question_id, $answer_index): void
    {
        if ($answer_id) {
            $this->dialog()
                ->question('Figyelmeztetés!', 'Biztosan törölni szeretnéd a választ?')
                ->confirm('Törlés', 'destroyAnswer', $answer_id)
                ->cancel('Mégse', 'cancelled', 'A válasz törlésre visszavonásra került.')
                ->send();
        } else {
            $this->removeAnswer($question_id, $answer_index);
        }
    }

    public function addExam(): void
    {
        $validated = $this->validate([
            'exam_name' => ['string', 'min:3', 'max:256'],
            'attempt_count' => ['integer', 'min:1', 'max:256'],
        ]);

        Exam::create([
            'guild_guild_id' => $this->guild->guild_id,
            'name' => $validated['exam_name'],
            'attempt_count' => $validated['attempt_count'],
            'min_pass_score' => 1,
        ]);

        $this->toast()->success('Sikeresen létrehoztál a(z) ' . $validated['exam_name'] . ' vizsgát.')->send();
        $this->dispatch('resetPage');
    }

    public function saveExam()
    {
        $validated = $this->validate([
            'exam_name' => ['string', 'min:3', 'max:256'],
            'attempt_count' => ['integer', 'min:1', 'max:256'],
            'min_pass_score' => ['integer', 'min:1', 'max:256'],
            'minute_per_task' => ['integer', 'min:1', 'max:256'],
            'q_number' => ['integer', 'min:1', 'max:256'],
            'exam_visible' => ['boolean'],
            'role_whitelist' => ['array'],
        ]);

        if ($validated['exam_name'] && $validated['attempt_count'] && $validated['min_pass_score']) {
            $this->selected_exam->update([
                'name' => $validated['exam_name'],
                'minute_per_task' => $validated['minute_per_task'],
                'attempt_count' => $validated['attempt_count'],
                'q_number' => $validated['q_number'],
                'min_pass_score' => $validated['min_pass_score'],
                'role_whitelist' => $validated['role_whitelist'],
                'visible' => $validated['exam_visible'],
            ]);

            $this->toast()->success('Sikeresen frissítve a vizsga adatai.')->send();
        } else {
            $this->toast()->error('A vizsga adatainak frissítése sikertelen volt.')->send();
        }
    }

    public function deleteExam(): void
    {
        $this->dialog()
            ->question('Figyelmeztetés!', 'Biztosan törölni szeretnéd az egész vizsgát?')
            ->confirm('Törlés', 'destroyExam')
            ->cancel('Mégse', 'cancelled', 'A vizsga törlésre visszavonásra került.')
            ->send();
    }

    public function destroyExam(): void
    {
        ExamAnswer::whereIn('exam_question_id',
            $this->selected_exam->questions()->pluck('id')
        )->delete();
        $this->selected_exam->questions()->delete();
        $this->selected_exam->results()->delete();
        $this->selected_exam->delete();
        $this->resetSelectedExam();
        $this->toast()->success('Sikeresen törö a vizsga adatai.')->send();
    }

    private function getRoles()
    {
        $roles = cache()->remember($this->guild->guild_id . '_roles', 15, function () {
            return getGuildData($this->guild->guild_id, 'roles');
        });

        return collect($roles)
            ->sortBy('position')
            ->map(function ($role) {
                return [
                    'label' => $role['name'],
                    'value' => $role['id'],
                ];
            })->toArray();
    }
}; ?>

<div>
    @empty($selected_exam)
        <div class="flex justify-end w-full">
            <x-button.circle icon="plus" color="green" x-on:click="$modalOpen('add-exam')"/>
        </div>
        <x-modal id="add-exam" x-on:open="$focusOn('name')" title="Vizsga létrehozása">
            <x-card>
                <div class="flex flex-col gap-4">
                    <x-input id="name" label="Vizsga neve" wire:model.live.debounce="exam_name"/>
                    <x-number label="Egy feladatra jutó idő (percben)" max="256" wire:model.live.debounce="minute_per_task"/>
                    <x-number label="Maximum próbálkozások száma" max="256" wire:model.live.debounce="attempt_count"/>
                </div>
            </x-card>
            <x-slot:footer>
                <div class="flex justify-end gap-2">
                    <x-button text="Mégse" x-on:click="$modalClose('add-exam')"/>
                    <x-button text="Létrehozás" wire:click="addExam" x-on:click="$modalClose('add-exam')" color="green"/>
                </div>
            </x-slot:footer>
        </x-modal>
        @livewire('exam.manager-table')
    @endempty
    @isset($selected_exam)
        <div class="flex flex-col gap-4">
            <x-card>
                <x-slot:header>
                    Vizsga adatai
                </x-slot:header>
                <div class="flex flex-wrap w-full justify-between items-center space-y-4">
                    <div class="grid grid-cols-1 lg:grid-cols-6 items-center gap-4">
                        <x-input label="Vizsga neve" wire:model.live.debounce="exam_name"/>
                        <x-number label="Feltett kérdések száma" max="256" wire:model.live.debounce="q_number"/>
                        <x-number label="Minimum pontszám" max="256" wire:model.live.debounce="min_pass_score"/>
                        <x-input readonly label="Összes pontszám" :value="$selected_exam->questions()->count()"/>
                        <x-number label="M. Prbálkozások száma" max="256" wire:model.live.debounce="attempt_count"/>
                        <x-number label="Egy feladatra jutó idő (percben)" max="256" wire:model.live.debounce="minute_per_task"/>
                        <x-select.styled wire:model="role_whitelist" :options="$this->roles" multiple searchable/>
                        <x-toggle label="Látható" wire:model.live.debounce="exam_visible" />
                    </div>
                    <div class="flex flex-wrap items-center gap-4">
                        <x-button.circle icon="bookmark" wire:click="saveExam"/>
                        <x-button.circle icon="trash" color="red" wire:click="deleteExam"/>
                        <x-button.circle icon="arrow-long-left" wire:click="resetSelectedExam"/>
                    </div>
                </div>
            </x-card>

            @foreach($question_answer as $question)
                <x-card>
                    <x-slot:header>
                        <div class="flex flex-wrap gap-6 items-center">
                            <x-textarea label="Kérdés" wire:model.live="editing_data.{{$question->id}}.question"/>
                            <div class="flex flex-wrap items-center gap-2">
                                <x-button.circle icon="bookmark" wire:click="saveQuestion('{{$question->id}}')"/>
                                <x-button.circle icon="trash" color="red"
                                                 wire:click="deleteQuestion('{{$question->id}}')"/>
                            </div>
                        </div>
                    </x-slot:header>
                    <div class="flex flex-wrap items-center gap-4">
                        @foreach($editing_data[$question->id]['answers'] ?? [] as $index => $answer)
                            <div class="flex flex-col gap-2">
                                <x-textarea :label="'Válasz - ' . $index+1"
                                            wire:model.live="editing_data.{{$question->id}}.answers.{{$index}}.answer"/>
                                <div class="flex items-center justify-between gap-4">
                                    <x-checkbox color="green" label="Helyes válasz"
                                                wire:model.live="editing_data.{{$question->id}}.answers.{{$index}}.correct"
                                                sm/>
                                    @if($answer['is_new'])
                                        <x-badge text="Nincs mentve" color="orange" xs/>
                                    @else
                                        <x-badge text="Elmentve" color="green" xs/>
                                    @endif
                                    <x-button.circle
                                        wire:click="deleteAnswer({{$answer['id']}}, {{$question->id}}, {{$index}})"
                                        color="red" icon="trash" sm/>
                                </div>
                            </div>
                        @endforeach
                        <x-button.circle wire:click="addNewAnswer({{ $question->id }})" icon="plus" md/>
                    </div>
                </x-card>
            @endforeach

            @foreach($editing_data as $question_id => $question_data)
                @if(isset($question_data['is_new']) && $question_data['is_new'])
                    <x-card>
                        <x-slot:header>
                            <div class="flex flex-wrap justify-between gap-4 items-center">
                                <x-input label="Új kérdés" wire:model.live="editing_data.{{$question_id}}.question"/>
                                <div class="flex gap-2 items-center">
                                    <x-badge text="Nincs mentve" color="orange"/>
                                    <x-button.circle icon="bookmark" wire:click="saveQuestion('{{$question_id}}')"/>
                                    <x-button.circle icon="trash" color="red"
                                                     wire:click="removeNewQuestion('{{$question_id}}')"/>
                                </div>
                            </div>
                        </x-slot:header>
                        <div class="flex flex-wrap items-center gap-4">
                            @foreach($question_data['answers'] ?? [] as $index => $answer)
                                <div class="flex flex-col gap-2">
                                    <x-textarea :label="'Válasz - ' . $index+1"
                                                wire:model.live="editing_data.{{$question_id}}.answers.{{$index}}.answer"/>
                                    <div class="flex items-center justify-between gap-4">
                                        <x-checkbox color="green" label="Helyes válasz"
                                                    wire:model.live="editing_data.{{$question_id}}.answers.{{$index}}.correct"
                                                    sm/>
                                        <x-badge text="Nincs mentve" color="orange" xs/>
                                        <x-button.circle
                                            wire:click="deleteAnswer({{$answer['id']}}, {{$question_id}}, {{$index}})"
                                            color="red" icon="trash" sm/>
                                    </div>
                                </div>
                            @endforeach
                            <x-button.circle wire:click="addNewAnswer('{{ $question_id }}')" icon="plus" md/>
                        </div>
                    </x-card>
                @endif
            @endforeach
            <x-card>
                <div class="flex items-center justify-center w-full">
                    <x-button text="Kérdés hozzáadása" icon="plus" color="green" wire:click="addNewQuestion"/>
                </div>
            </x-card>
        </div>
    @endisset
</div>
