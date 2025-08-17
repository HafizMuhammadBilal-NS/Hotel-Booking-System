<?php
$roomType = $_GET['room'] ?? '';
$checkin = $_GET['date'] ?? date('Y-m-d');

try {
  $pdo = new PDO("mysql:host=127.0.0.1;dbname=DEN102_A1;charset=utf8mb4","root","",[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
  ]);
} catch (Throwable $e) {
  http_response_code(500);
  echo "<p style='color:red'>DB error: ".$e->getMessage()."</p>";
  exit;
}

$available = null; $totalRooms = null; $booked = null;
if ($roomType) {
  $q = $pdo->prepare("SELECT total_rooms, price FROM rooms WHERE room_type = ?");
  $q->execute([$roomType]);
  $room = $q->fetch();

  if ($room) {
    $totalRooms = (int)$room['total_rooms'];
    $price = $room['price'];

    $q2 = $pdo->prepare("SELECT COUNT(*) AS booked FROM bookings WHERE room_type=? AND checkin_date=?");
    $q2->execute([$roomType, $checkin]);
    $booked = (int)$q2->fetch()['booked'];
    $available = $totalRooms - $booked;
  }
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Availability – Aurora Hotel</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="../css/style.css" rel="stylesheet">
</head>
<body class="page">

  <div class="topbar">
    <div class="container inner">
      <div>
        <div class="logo">Aurora Hotel</div>
        <div class="breadcrumbs"><a href="../index.html" style="color:#fff;opacity:.8">Home</a> › Availability</div>
      </div>
      <a class="btn btn-ghost" href="../index.html#rooms">Back to Rooms</a>
    </div>
  </div>

  <main class="container">
    <div class="row two">
      <section class="card">
        <div class="card-title">Check Availability</div>
        <form method="GET" class="row" style="grid-template-columns:1fr 1fr auto; gap:12px">
          <input type="hidden" name="room" value="<?= htmlspecialchars($roomType ?: 'Standard Twin') ?>">
          <div class="form-group">
            <label class="label">Room Type</label>
            <input class="input" type="text" value="<?= htmlspecialchars($roomType ?: 'Standard Twin') ?>" readonly>
          </div>
          <div class="form-group">
            <label class="label">Check-in Date</label>
            <input class="input" type="date" name="date" id="date" value="<?= htmlspecialchars($checkin) ?>" required>
          </div>
          <div class="form-group" style="align-self:end">
            <button class="btn btn-secondary" type="submit">Check</button>
          </div>
        </form>

        <?php if ($roomType && $totalRooms !== null): ?>
          <div class="card" style="margin-top:18px">
            <?php if ($available > 0): ?>
              <div class="status">
                <span class="dot green"></span>
                <span class="badge badge-success">Available</span>
              </div>
              <p style="margin-top:8px">
                <strong><?= htmlspecialchars($roomType) ?></strong> on
                <strong><?= htmlspecialchars($checkin) ?></strong> —
                <strong><?= $available ?></strong> room(s) left.
              </p>
              <div class="actions" style="margin-top:12px">
                <a class="btn btn-primary" href="../booking.html?room=<?= urlencode($roomType) ?>&date=<?= urlencode($checkin) ?>">Book Now</a>
                <a class="btn btn-ghost" href="?room=<?= urlencode($roomType) ?>">Check another date</a>
              </div>
            <?php else: ?>
              <div class="status">
                <span class="dot red"></span>
                <span class="badge badge-danger">Fully Booked</span>
              </div>
              <p style="margin-top:8px">
                No rooms for <strong><?= htmlspecialchars($roomType) ?></strong> on
                <strong><?= htmlspecialchars($checkin) ?></strong>.
              </p>
              <a class="btn btn-ghost" href="?room=<?= urlencode($roomType) ?>">Try a different date</a>
            <?php endif; ?>
          </div>
        <?php elseif($roomType && $totalRooms === null): ?>
          <div class="card" style="margin-top:18px">
            <div class="status">
              <span class="dot red"></span>
              <span class="badge badge-danger">Unknown Room</span>
            </div>
            <p style="margin-top:8px">The room type you selected does not exist.</p>
          </div>
        <?php endif; ?>
      </section>

      <aside class="card summary">
        <div class="card-title">Tips</div>
        <ul style="line-height:1.8; padding-left:18px; color:#555">
          <li>Pick a future date to ensure availability.</li>
          <li>Click <strong>Book Now</strong> to prefill the booking form.</li>
          <li>Availability updates instantly when you change the date.</li>
        </ul>
      </aside>
    </div>
  </main>

  <script>
    (function(){
      const d = document.getElementById('date');
      if(!d) return;
      const tz = new Date();
      const iso = tz.toISOString().slice(0,10);
      if(!d.value) d.val
