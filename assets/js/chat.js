document.addEventListener("DOMContentLoaded", function () {
    const chatForm = document.getElementById("chat-form");
    const chatBox = document.getElementById("chat-box");

    chatForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const formData = new FormData(chatForm);
        fetch("send_message.php", {
            method: "POST",
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === "success") {
                const message = document.createElement("div");
                message.classList.add("message", "sent");
                message.innerHTML = `<p>${formData.get("message")}</p><span>Just now</span>`;
                chatBox.appendChild(message);
                chatForm.reset();
                chatBox.scrollTop = chatBox.scrollHeight;
            }
        });
    });

    // Auto-refresh messages
    setInterval(() => {
        fetch(window.location.href)
        .then(response => response.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, "text/html");
            document.getElementById("chat-box").innerHTML = doc.getElementById("chat-box").innerHTML;
        });
    }, 5000);
});
