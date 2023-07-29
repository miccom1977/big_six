<?php

namespace App\Services;

use App\Models\Exercise;
use App\Models\Repetition;
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
     * @param int $stepTraining nr. zadania do wykonania
     * @return array
     */
    public function getExercisesList(int $workStep, int $dayOfTheWeek, int $stepTraining): array
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
        return array($workDay, $exercisesToDo[$stepTraining] ?? '');
    }

    /** Metoda zwraca poziom zaawansowania treningu
     * 1. Świeża Krew
     * 2. Dobre Sprawowanie
     * 3. Weteran
     * 4. Odosobnienie
     * 5. Supermax
     * @param array $exStages Poziom wykonania poszczególnych zadań Wielkiej Szóstki
     * @param int $iAllTrainings Liczba wykonanych już treningów
     * @return int
     */
    public function getWorkStep(array $exStages, int $iAllTrainings): int
    {
        $workStep = 0;
        switch (count($exStages)) {
            case 2 && $iAllTrainings > 0:
                $workStep = 1;
                break;
            case 3:
                $workStep = 2;
                break;
            case 6:
                $trainingLevel = array_sum($exStages);
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

    /** Metoda zwaraca zadanie do wykonania
     * @param mixed $singleExplode
     * @return array
     */
    public function getExercises(mixed $singleExplode): array
    {
        $exercises = [];
        $maxTraining = Exercise::where('user_id', Auth::user()->id)
            ->where('work_id', $singleExplode)->orderBy('ex_id', 'DESC')->first();
        if ($maxTraining) {
            $allStepTrainings = Exercise::where('user_id', Auth::user()->id)
                ->where('work_id', $singleExplode)->where('ex_id', $maxTraining->ex_id)->whereDate('created_at', Carbon::today())->get();
            $step1 = $step2 = $step3 = 0;
            foreach ($allStepTrainings as $singleTraining) {
                switch ($singleTraining->step) {
                    case 1:
                        $step1++;
                        break;
                    case 2:
                        $step2++;
                        break;
                    case 3:
                        $step3++;
                        break;
                }
            }
            $work_id = $singleExplode . '_' . $maxTraining->ex_id;
            $step2Done = $step3Done = 0;
            $repetition = Repetition::where('work_id', $work_id)
                ->where('step', 1)->first();
            $step1Done = $repetition->series <= $step1 ? 1 : 0;
            $repetitions = $repetition->repetitions;
            $seriesEnd = $step1;
            if ($step1Done) {
                $repetition = Repetition::where('work_id', $work_id)
                    ->where('step', 2)->first();
                $step2Done = $repetition->series <= $step2 ? 1 : 0;
                $repetitions = $repetition->repetitions;
                $seriesEnd = $step2;

                if ($step2Done) {
                    $repetition = Repetition::where('work_id', $work_id)
                        ->where('step', 3)->first();
                    $step3Done = $repetition->series <= $step3 ? 1 : 0;
                    $repetitions = $repetition->repetitions;
                    $seriesEnd = $step3;
                }
            }

            $exercises[] = [
                'name' => $work_id,
                'step1Done' => $step1Done,
                'step2Done' => $step2Done,
                'step3Done' => $step3Done,
                'repetitions' => $repetitions,
                'seriesEnd' => $seriesEnd,
                'seriesToDo' => $repetition->series,
            ];
        } else {
            $work_id = $singleExplode . '_1';
            $repetition = Repetition::where('work_id', $work_id)
                ->where('step', 1)->first();
            $repetitions = $repetition->repetitions;
            $exercises[] = [
                'name' => $work_id,
                'step1Done' => 0,
                'step2Done' => 0,
                'step3Done' => 0,
                'repetitions' => $repetitions,
                'seriesEnd' => 0,
                'seriesToDo' => $repetition->series,
            ];
        }
        return $exercises;
    }
}
