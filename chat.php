<?php session_start(); if (!isset($_SESSION['authorized'])) { header("Location: index.php"); exit; } ?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Geheimer Chat</title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div id="wrapper">
    <div id="header">
      <span id="online">Online: <span id="online-count">0</span></span>
      <span id="user">Du: <span id="nickname"></span></span>
      <button id="logout">Logout / Nick ändern</button>
    </div>

    <div id="messages"></div>

    <form id="form">
      <input type="text" id="message" placeholder="Nachricht schreiben..." maxlength="500" autocomplete="off" required>
      <button>Senden</button>
    </form>
  </div>

  <script>
    const NICK_KEY = "chat_nickname";
    let myNick = localStorage.getItem(NICK_KEY) || "";

    // Nickname abfragen
    while (!myNick || myNick.length > 20 || myNick.trim() === "") {
      myNick = prompt("Wähle deinen Nickname (max. 20 Zeichen):") || "";
      myNick = myNick.trim().substring(0,20);
    }
    localStorage.setItem(NICK_KEY, myNick);
    document.getElementById("nickname").textContent = myNick;

    const messagesDiv = document.getElementById("messages");
    const form = document.getElementById("form");
    const input = document.getElementById("message");
    let onlineUsers = new Set();

    // Nachricht senden
    form.addEventListener("submit", e => {
      e.preventDefault();
      if (input.value.trim() === "") return;

      fetch("api.php", {
        method: "POST",
        headers: {"Content-Type": "application/x-www-form-urlencoded"},
        body: `nick=${encodeURIComponent(myNick)}&msg=${encodeURIComponent(input.value.trim())}`
      });
      input.value = "";
    });

    // Logout-Button
    document.getElementById("logout").addEventListener("click", () => {
      localStorage.removeItem(NICK_KEY);
      location.reload();
    });

    // Alle 2 Sekunden Nachrichten laden + Online-Liste
    function load() {
      fetch("api.php?load=1")
        .then(r => r.json())
        .then(data => {
          messagesDiv.innerHTML = "";
          data.messages.forEach(msg => {
            const div = document.createElement("div");
            div.className = "msg";
            div.innerHTML = `<span class="time">[${msg.time}]</span> <b>${msg.nick}</b>: ${msg.text}`;
            messagesDiv.appendChild(div);
          });

          // Login/Logout Meldungen
          data.events.forEach(ev => {
            const div = document.createElement("div");
            div.className = "event";
            div.innerHTML = `<i>${ev.time} — ${ev.nick} hat den Chat ${ev.type === 'login' ? 'betreten' : 'verlassen'}</i>`;
            messagesDiv.appendChild(div);
          });

          // Online-Anzeige
          document.getElementById("online-count").textContent = data.online;

          // Auto-Scroll
          messagesDiv.scrollTop = messagesDiv.scrollHeight;
        });
    }

    // Alle 2 Sekunden aktualisieren + eigenes Login senden
    setInterval(load, 2000);
    load();

    // Eigenes Login einmalig senden
    fetch("api.php", {
      method: "POST",
      headers: {"Content-Type": "application/x-www-form-urlencoded"},
      body: `nick=${encodeURIComponent(myNick)}&login=1`
    });

    // Beim Schließen → Logout senden
    window.addEventListener("beforeunload", () => {
      navigator.sendBeacon("api.php", `nick=${encodeURIComponent(myNick)}&logout=1`);
    });
  </script>
</body>
</html>