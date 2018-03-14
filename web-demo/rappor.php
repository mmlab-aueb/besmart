<?php
    function permanent_randomized_response($true_answer)
    {
        $rappor_f = 50;
        $r = rand(0,99);
        if ($r < 100 - $rappor_f)
            return $true_answer;
        else if ($r >= 100 - $rappor_f && $r < (100 - $rappor_f) + (0.5* $rappor_f))
            return 1;
        else
            return 0;
    }
?>
