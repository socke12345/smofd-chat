<?php
header('Content-Type: application/json');
$file = 'messages.json';

// Sicher laden
$data = file_exists($file) ? json_decode(file_get_contents($file), true) : ['messages' => [], 'events' => [], 'online' => []];

// 12-Stunden-Cleanup
$cutoff = time() - 12*60*60;
$data['messages'] = array_filter($data['messages'], fn($m) => $m['delete_at'] > time());

// POST: Nachricht
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  parse_str(file_get_contents('php://input'), $post);

  $nick = htmlspecialchars(substr(trim($post['nick'] ?? ''), 0, 20));
  if (isset($post['msg']) && $nick && strlen($post['msg']) <= 500) {
    $msg = htmlspecialchars(trim($post['msg']));
    $data['messages'][] = [
      'nick' => $nick,
      'text' => $msg,
      'time' => date("H:i"),
      'delete_at' => time() + 12*60*60
    ];
  }

  // Login/Logout Events + Online-Liste
  if (!empty($post['login'])) {
    $data['events'][] = ['nick' => $nick, 'type' => 'login', 'time' => date("H:i")];
    $data['online'][$nick] = time();
  }
  if (!empty($post['logout'])) {
    $data['events'][] = ['nick' => $nick, 'type' => 'logout', 'time' => date("H:i")];
    unset($data['online'][$nick]);
  }
}

// Online-Cleanup (länger als 2 Minuten inaktiv = offline)
foreach($data['online'] as $n => $t) {
  if ($t < time() - 120) unset($data['online'][$n]);
}
if (!empty($_POST)) {
  file_put_contents($file, json_encode($data));
  exit;
}

// GET: Daten liefern
if (isset($_GET['load'])) {
  echo json_encode([
    'messages' => array_values($data['messages']),
    'events'   => array_slice(array_values($data['events']), -30), // letzte 30 Events
    'online'   => count($data['online'])
  ]);
}