
function toggleMenu(element) {
    const card = element.closest(".menu-card");

    card.classList.toggle("active");
}


const chatForm = document.getElementById("chatForm");
const chatInput = document.getElementById("chatInput");
const chatMessages = document.getElementById("chatMessages");

if (chatForm) {
    chatForm.addEventListener("submit", function (e) {
        e.preventDefault();

        const message = chatInput.value.trim();

        if (!message) return;

        // Felhasználói üzenet
        const userMessage = document.createElement("div");
        userMessage.className = "chat-message user-message";
        userMessage.textContent = message;

        chatMessages.appendChild(userMessage);

        chatInput.value = "";

        chatMessages.scrollTop = chatMessages.scrollHeight;
    });
}
