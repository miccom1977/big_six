<div>
    <div id="startTraining" class="btn btn-primary" wire:click="startTimer($event.target.closest('.exerciseElement').getAttribute('work_id'))">{{ __('app.start_training') }}</div>
    <div id="stopTraining" class="btn btn-secondary d-none" wire:click="stopTimer(document.getElementById('timer').textContent)">{{ __('app.stop_training') }}</div>
    <div id="timer">{{ $timerTimestamp }}</div>
</div>
