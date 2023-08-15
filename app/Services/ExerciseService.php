<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\ExercisesStages;
use App\Models\Repetition;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Class ExerciseService
 * @package App\Services
 */
class ExerciseService
{
    /**
     * @param int $workStep Krok ćwiczenia
     * @param int $dayOfTheWeek Dzień treningowy
     * @param int $stepExercise nr. zadania do wykonania
     * @return array
     */
    public function getExercisesList(int $workStep, int $dayOfTheWeek, int $stepExercise): array
    {
        $workDay = [];
        $exercisesToDo = [];
        switch ($workStep) {
            case 0: // user jeszcze nie ustawił żadnego treningu, więc dajemy mu trening na dzisiaj
                // i zapisujemy nową sesję treningową
                $workDay = [$dayOfTheWeek];
                $exercisesToDo = [1, 4]; //zadania 1, 4, ale wybieramy pierwsze do pokazania
                break;
            case 1: // trening świeża krew
                $workDay = [1, 5];
                switch ($dayOfTheWeek) {
                    case 1:
                        $exercisesToDo = [1, 4];//zadania 1, 4, ale wybieramy pierwsze do pokazania
                        break;
                    case 5:
                        $exercisesToDo = [2, 3];//zadania 2, 3, ale wybieramy pierwsze do pokazania
                        break;
                }
                break;
            case 2: // trening Dobre Sprawowanie
                $workDay = [1, 3, 5];
                switch ($dayOfTheWeek) {
                    case 1:
                        $exercisesToDo = [1, 4];//zadania 1, 4, ale wybieramy pierwsze do pokazania
                        break;
                    case 3:
                        $exercisesToDo = [2, 3];//zadania 2, 3, ale wybieramy pierwsze do pokazania
                        break;
                    case 5:
                        $exercisesToDo = [5, 6];//zadania 5, 6, ale wybieramy pierwsze do pokazania
                        break;
                }
                break;
            case 3: // Trening Weteran
                $workDay = [1, 2, 3, 4, 5, 6];
                switch ($dayOfTheWeek) {
                    case 1:
                        $exercisesToDo = [3];
                        break;
                    case 2:
                        $exercisesToDo = [5];
                        break;
                    case 3:
                        $exercisesToDo = [6];
                        break;
                    case 4:
                        $exercisesToDo = [4];
                        break;
                    case 5:
                        $exercisesToDo = [2];
                        break;
                    case 6:
                        $exercisesToDo = [1];
                        break;
                }
                break;
            case 4: // trening Odosobnienie
            case 5: // trening Supermax
                $workDay = [1, 2, 3, 4, 5, 6];
                switch ($dayOfTheWeek) {
                    case 1:
                    case 4:
                        $exercisesToDo = [2, 3];//zadania 2, 3, ale wybieramy pierwsze do pokazania
                        break;
                    case 2:
                    case 5:
                        $exercisesToDo = [1, 4];//zadania 1, 4, ale wybieramy pierwsze do pokazania
                        break;
                    case 3:
                    case 6:
                        $exercisesToDo = [5, 6];//zadania 5, 6, ale wybieramy pierwsze do pokazania
                        break;
                }
                break;
        }
        return $exercisesToDo;
    }

    /** Metoda zwraca poziom zaawansowania treningu
     * 1. Świeża Krew
     * 2. Dobre Sprawowanie
     * 3. Weteran
     * 4. Odosobnienie
     * 5. Supermax
     * @param array $exStages Poziom wykonania poszczególnych zadań Wielkiej Szóstki
     * @return int
     */
    public function getWorkStep(array $exStages): int
    {
        $workStep = 0;
        $trainingLevel = array_sum($exStages);
        switch (count($exStages)) {
            case 4:
                $workStep = $trainingLevel < 14 ? 1 : 2;
                break;
            case 6:
                switch ($trainingLevel) {
                    case $trainingLevel > 35:
                        $workStep = 3;
                        break;
                    case $trainingLevel > 42:
                        $workStep = 4;
                        break;
                    case $trainingLevel > 48:
                        $workStep = 5;
                        break;
                }
                break;
        }
        return $workStep;
    }
/*
    /** Metoda zwraca zadanie do wykonania
     * @param array $exercisesToDo Lista zadań
     * @return array
     */
    /*
    public function getExercises(array $exercisesToDo): array
    {
        $aExercises = [];
        $exercises = [];
        // pobieramy poziomy ćwiczeń (wejście, trening, przejście)
        $exercisesStages = ExercisesStages::where('user_id', Auth::user()->id)->get();
        // generujemy string z kompletem zadanie_poziom
        foreach ($exercisesToDo as $singleExercise) {
            $step = ($exercisesStages[$singleExercise]['step'] ?? 1);
            $repetition = Repetition::where('id', $singleExercise)->where('step', $step)->first();
            $aExercises[$singleExercise] = [
                'step' => $step,
                'series' => $repetition->series,
            ];
            // pobieramy ilość wykonanych treningów
            $allStepTrainings = Exercise::where('user_id', Auth::user()->id)
                ->whereDate('created_at', Carbon::today())
                ->where('work_id', $singleExercise)->where('step', $step)->count();
            if ($allStepTrainings < $repetition->series * 2) {
                $exercises[] = [
                    'work_id' => $singleExercise,
                    'step' => $step,
                    'name' => $singleExercise,
                    'repetitions' => $repetition->repetitions,
                    'seriesEnd' => $repetition->series * 2,
                    'seriesToDo' => ($repetition->series * 2) - $allStepTrainings
                ];
            }
        }

        return $exercises;
    }
    */
    public function getExercises(array $exercisesToDo): array
    {
        $authUserId = Auth::user()->id;
        $exercisesStages = ExercisesStages::where('user_id', $authUserId)->get();
        $repetitions = Repetition::whereIn('id', $exercisesToDo)->get();

        // Zbierz unikalne work_id i step z $exercisesToDo
        $exerciseData = [];
        foreach ($exercisesToDo as $singleExercise) {
            $step = ($exercisesStages[$singleExercise]['step'] ?? 1);
            $exerciseData[$singleExercise][$step] = $step;
        }

        // Zbierz unikalne work_id i step do tablic, żeby użyć ich w zapytaniu
        $workIds = array_keys($exerciseData);
        $steps = Arr::flatten($exerciseData);

        // Pobierz dane dotyczące wszystkich ćwiczeń i etapów w jednym zapytaniu
        $allStepTrainings = Exercise::whereIn('work_id', $workIds)
            ->where('user_id', $authUserId)
            ->whereDate('created_at', Carbon::today())
            ->whereIn('step', $steps)
            ->selectRaw('work_id, step, COUNT(*) as count')
            ->groupBy('work_id', 'step')
            ->get()
            ->keyBy(function ($item) {
                return $item->work_id . '_' . $item->step;
            });

        $exercises = [];
        foreach ($exercisesToDo as $singleExercise) {
            $step = ($exercisesStages[$singleExercise]['step'] ?? 1);
            $repetition = $repetitions->firstWhere('id', $singleExercise);

            $key = $singleExercise . '_' . $step;
            $count = $allStepTrainings[$key]->count ?? 0;

            $exercises[] = [
                'work_id' => $singleExercise,
                'step' => $step,
                'name' => $singleExercise,
                'repetitions' => $repetition->repetitions,
                'seriesEnd' => $repetition->series * 2,
                'seriesDoIt' => $count,
                'name' => $key,
                'ex_id' => $singleExercise
            ];
        }
        return $exercises;
    }
}
