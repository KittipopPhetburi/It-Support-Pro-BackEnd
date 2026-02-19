<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

$incidents = App\Models\Incident::orderByDesc('id')->take(3)->get(['id','ticket_id','title','status','satisfaction_rating','satisfaction_comment','satisfaction_date']);
foreach ($incidents as $i) {
    echo "ID:{$i->id} | Ticket:{$i->ticket_id} | Status:{$i->status} | Rating:{$i->satisfaction_rating} | Comment:{$i->satisfaction_comment}\n";
}
