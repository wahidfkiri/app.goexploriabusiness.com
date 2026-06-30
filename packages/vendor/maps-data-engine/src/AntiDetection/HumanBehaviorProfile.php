<?php

namespace Vendor\MapsDataEngine\AntiDetection;

class HumanBehaviorProfile
{
    public function export(): array
    {
        return [
            'mouse_move_steps' => random_int(8, 16),
            'scroll_pause_min' => random_int(450, 900),
            'scroll_pause_max' => random_int(1200, 2600),
            'typing_delay_min' => random_int(60, 110),
            'typing_delay_max' => random_int(130, 240),
            'open_delay_min' => random_int(700, 1500),
            'open_delay_max' => random_int(1800, 3200),
        ];
    }
}
