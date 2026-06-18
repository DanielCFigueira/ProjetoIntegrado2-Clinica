<?php
$lines = file('C:/Users/Lased/.gemini/antigravity/brain/cde19dfc-4840-4713-9302-95bc38dd8a8b/.system_generated/logs/transcript.jsonl');
foreach($lines as $line) {
    $d = json_decode($line, true);
    if ($d['type'] === 'PLANNER_RESPONSE' && isset($d['content']) && strpos($d['content'], 'BAIXO') !== false) {
        echo substr($d['content'], 0, 4000);
        break;
    }
}
