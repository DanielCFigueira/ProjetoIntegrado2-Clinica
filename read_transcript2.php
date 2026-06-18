<?php
$lines = file('C:/Users/Lased/.gemini/antigravity/brain/cde19dfc-4840-4713-9302-95bc38dd8a8b/.system_generated/logs/transcript.jsonl');
foreach($lines as $line) {
    $d = json_decode($line, true);
    if (isset($d['content']) && strpos($d['content'], 'Relatório Diagnóstico de Bugs') !== false) {
        $pos = strpos($d['content'], 'BAIXO');
        if ($pos !== false) {
            echo substr($d['content'], $pos, 4000); // 4000 instead of 2000
            break;
        }
    }
}
