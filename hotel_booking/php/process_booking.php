<?php
function header_html($title='Aurora Hotel'){
  echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">';
  echo '<title>'.htmlspecialchars($title).'</title><link rel="stylesheet" href="../css/style.css"></head><body class="page">';
  echo '<div class="topbar"><div class="container inner"><div><div class="logo">Aurora Hotel</div><div class="breadcrumbs"><a href="../index.html" style="color:#fff;opacity:.8">Home</a> › Confirmation</div></div><a class="btn btn-ghost" href="../index.html#rooms">Back to Rooms</a></div></div>';
  echo '<main class="container"><section class="card">';
}
function footer_html(){
  echo '</section></main></body></html>';
}

try {
  $pdo = new PDO("mysql:host=127.0.0.1;dbname=DEN102_A1;charset=utf8mb4","root","",[
    PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC
  ]);
} catch (Throwable $e) {
  header_html("Error");
  echo "<h2 class='card-title'>Database Error</h2><p style='color:#c0392b'>".$e->getMessage()."</p>";
  footer_html(); exit;
}

$name   = trim($_POST['customer_name'] ?? '');
$email  = trim($_POST['email'] ?? '');
$phone  = trim($_POST['phone'] ?? '');
$date   = $_POST['checkin_date'] ?? '';
$room   = $_POST['room_type'] ?? '';
$persons= (int)($_POST['persons'] ?? 0);

function fail($msg){
  header_html("Invalid Submission");
  echo "<div class='status'><span class='dot red'></span><span class='badge badge-danger'>Invalid</span></div>";
  echo "<p style='margin-top:10px;color:#c0392b'>".$msg."</p>";
  echo "<div class='actions' style='margin-top:14px'><a class='btn btn-ghost' href='../booking.html?room=".urlencode($_POST['room_type'] ?? 'Standard Twin')."'>Go back</a></div>";
  footer_html(); exit;
}

if (!preg_match("/^[A-Za-z]+(?:\s+[A-Za-z]+)+$/", $name)) fail("Please include first and last name.");
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) fail("Invalid email address.");
if (!preg_match("/^04\d{8}$/", $phone)) fail("Phone must start with 04 and be 10 digits.");
if (!$date) fail("Invalid check-in date.");

$cap = 2;
if (str_contains($room,'Superior') || str_contains($room,'Deluxe') || str_contains($room,'Executive Suite')) $cap = 3;
if (str_contains($room,'Presidential')) $cap = 5;
if ($persons < 1 || $persons > $cap) fail("This room allows a maximum of $cap person(s).");

$r = $pdo->prepare("SELECT total_rooms, price FROM rooms WHERE room_type=?");
$r->execute([$room]);
$roomRow = $r->fetch();
if (!$roomRow) fail("Unknown room type selected.");

$tot = (int)$roomRow['total_rooms'];
$c = $pdo->prepare("SELECT COUNT(*) AS booked FROM bookings WHERE room_type=? AND checkin_date=?");
$c->execute([$room,$date]);
$booked = (int)$c->fetch()['booked'];
$avail = $tot - $booked;

if ($avail <= 0){
  header_html("No Availability");
  echo "<div class='status'><span class='dot red'></span><span class='badge badge-danger'>Fully Booked</span></div>";
  echo "<p style='margin-top:10px'>No <strong>".htmlspecialchars($room)."</strong> rooms available on <strong>".htmlspecialchars($date)."</strong>.</p>";
  echo "<div class='actions' style='margin-top:14px'>
          <a class='btn btn-ghost' href='../php/check_availability.php?room=".urlencode($room)."'>Check another date</a>
          <a class='btn btn-secondary' href='../index.html#rooms'>Choose a different room</a>
        </div>";
  footer_html(); exit;
}

$ins = $pdo->prepare("INSERT INTO bookings (customer_name, email, phone, checkin_date, room_type, persons) VALUES (?,?,?,?,?,?)");
$ins->execute([$name,$email,$phone,$date,$room,$persons]);

$ref = 'AUR-'.date('ymd').'-'.strtoupper(substr(md5(uniqid((string)mt_rand(), true)),0,6));
$price = number_format((float)$roomRow['price'], 2);

header_html("Booking Confirmed");
echo "<div class='status'><span class='dot green'></span><span class='badge badge-success'>Booking Confirmed</span></div>";
echo "<p style='margin-top:10px;color:#555'>Your booking was successful. A confirmation has been recorded.</p>";

echo "<div class='card' style='margin-top:16px'>
        <div class='card-title'>Reservation Details</div>
        <div class='row' style='grid-template-columns:1fr 1fr; gap:10px'>
          <p><strong>Reference:</strong> $ref</p>
          <p><strong>Date:</strong> ".htmlspecialchars($date)."</p>
          <p><strong>Name:</strong> ".htmlspecialchars($name)."</p>
          <p><strong>Email:</strong> ".htmlspecialchars($email)."</p>
          <p><strong>Phone:</strong> ".htmlspecialchars($phone)."</p>
          <p><strong>Room:</strong> ".htmlspecialchars($room)."</p>
          <p><strong>Guests:</strong> ".htmlspecialchars((string)$persons)."</p>
          <p><strong>Price:</strong> \$$price / night</p>
        </div>
      </div>";

echo "<div class='actions' style='margin-top:16px'>
        <button class='btn btn-ghost' onclick='window.print()'>Print</button>
        <a class='btn btn-primary' href='../index.html#rooms'>Home</a>
        <a class='btn btn-secondary' href='../booking.html?room=".urlencode($room)."&date=".urlencode($date)."'>New Booking</a>
      </div>";

footer_html();