<?php

namespace App\Http\Livewire;

use App\Models\Exercise;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Timer extends Component
{
    public string $timerTimestamp = '';
    protected $listeners = ['prepareNextTraining' => 'prepareNextTraining'];

    public function render()
    {
        return view('livewire.timer');
    }



    public function startTimer()
    {
        $this->timerTimestamp = gmdate("H:i:s", 120);
        $this->dispatchBrowserEvent('start_timer', ['time' => 120]);
    }

    public function stopTimer(string $timer)
    {
        $totalSeconds = $this->timeToSeconds($timer);
        $this->dispatchBrowserEvent('stop_timer', ['time' => $totalSeconds]);
    }

    public function prepareNextTraining(array $exData) {
        Exercise::create([
            'work_id' => $exData['work_id'],
            'user_id' => Auth::user()->id,
            'step' => $exData['step'],
            'ex_id' => $exData['ex_id'],
        ]);
        //$user = User::find(Auth::user()->id);
        //$user->stage = '1_1_1_1';
        //$user->save();
        return redirect()->to('/home');
        //$this->dispatchBrowserEvent('start_next_exercise', ['title' => 'startujemy z nowym zadaniem']);
    }

    private function timeToSeconds($time): float|int
    {
        $parts = explode(':', $time);

        if (count($parts) === 3) {
            $hours = (int) $parts[0];
            $minutes = (int) $parts[1];
            $seconds = (int) $parts[2];

            return ($hours * 3600) + ($minutes * 60) + $seconds;
        }

        return 0; // Nieprawidłowy format czasu
    }
}
