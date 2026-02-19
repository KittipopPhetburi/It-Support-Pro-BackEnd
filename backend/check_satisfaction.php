<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Satisfaction Surveys ===\n";
$surveys = \App\Models\SatisfactionSurvey::all();
if ($surveys->isEmpty()) {
    echo "No surveys found.\n";
} else {
    foreach ($surveys as $s) {
        echo "ID:{$s->id} | ticket_id:{$s->ticket_id} | rating:{$s->rating} | feedback:{$s->feedback} | submitted_at:{$s->submitted_at}\n";
    }
}

echo "\n=== Last 5 Incidents (satisfaction fields) ===\n";
$incidents = \App\Models\Incident::orderByDesc('id')->take(5)->get();
foreach ($incidents as $i) {
    echo "ID:{$i->id} | ticket_id:{$i->ticket_id} | status:{$i->status} | sat_rating:{$i->satisfaction_rating} | sat_comment:{$i->satisfaction_comment}\n";
}
