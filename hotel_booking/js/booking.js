document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("bookingForm");
  const params = new URLSearchParams(window.location.search);
  const roomInput = document.getElementById("room_type");
  const dateInput = document.getElementById("date");
  const personsInput = document.getElementById("persons");

  // Map room → capacity, price, image
  const ROOMS = {
    "Standard Twin": { cap:2, price:120, img:"images/standard-twin.jpg", features:["2 single beds","20m²","Free Wi-Fi"] },
    "Executive Twin": { cap:2, price:150, img:"images/executive-twin.png", features:["2 queen beds","25m²","City view"] },
    "Superior Suite": { cap:3, price:180, img:"images/superior-suite.png", features:["King bed","35m²","Balcony"] },
    "Deluxe Suite": { cap:3, price:220, img:"images/deluxe-suite.png", features:["King bed","40m²","Pool view"] },
    "Executive Suite": { cap:3, price:260, img:"images/executive-suite.png", features:["King bed","45m²","Lounge access"] },
    "Presidential Suite": { cap:5, price:500, img:"images/presidential-suite.png", features:["2 king beds","60m²","Ocean view"] },
  };

  // Prefill
  const selRoom = params.get("room") || "Standard Twin";
  roomInput.value = selRoom;
  const today = new Date().toISOString().slice(0,10);
  dateInput.min = today;
  dateInput.value = params.get("date") || today;

  // Update summary panel
  function renderSummary() {
    const data = ROOMS[roomInput.value] || ROOMS["Standard Twin"];
    document.getElementById("roomTitle").textContent = roomInput.value;
    document.getElementById("roomPrice").textContent = `$${data.price}/night`;
    document.getElementById("roomImg").src = data.img;

    const ul = document.getElementById("roomFeatures");
    ul.innerHTML = "";
    data.features.forEach(f => {
      const li = document.createElement("li");
      li.textContent = f;
      ul.appendChild(li);
    });
  }
  renderSummary();

  // Inline validation helpers
  const err = (id, on) => {
    const grp = document.getElementById(id);
    if (!grp) return;
    if (on) grp.classList.add("error"); else grp.classList.remove("error");
  };

  // Validate capacity
  function checkCapacity() {
    const max = (ROOMS[roomInput.value] || {}).cap || 2;
    const val = parseInt(personsInput.value || "1", 10);
    const over = val > max || val < 1;
    document.getElementById("cap-error").textContent = over
      ? `This room allows a maximum of ${max} persons.`
      : "Exceeds room capacity.";
    err("grp-persons", over);
    return !over;
  }
  personsInput.addEventListener("input", checkCapacity);

  form.addEventListener("submit", (e) => {
    let ok = true;

    // Name: first + last
    const name = document.getElementById("name").value.trim();
    if (!/^[A-Za-z]+(?:\s+[A-Za-z]+)+$/.test(name)) { err("grp-name", true); ok = false; } else err("grp-name", false);

    // Email
    const email = document.getElementById("email").value.trim();
    if (!email.includes("@")) { err("grp-email", true); ok = false; } else err("grp-email", false);

    // Phone
    const phone = document.getElementById("phone").value.trim();
    if (!/^04\d{8}$/.test(phone)) { err("grp-phone", true); ok = false; } else err("grp-phone", false);

    // Date
    if (!dateInput.value || dateInput.value < today) { err("grp-date", true); ok = false; } else err("grp-date", false);

    // Capacity
    if (!checkCapacity()) ok = false;

    if (!ok) {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: "smooth" });
    }
  });
});
