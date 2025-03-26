document.addEventListener("DOMContentLoaded", function() {
    const radioButtons = document.querySelectorAll('input[name="emotion"]');
    const nextButtonContainer = document.querySelector('.form-button-container');
    const emotionForm = document.getElementById('emotionForm');

    const selectedEmotionNameInput = document.getElementById('selectedEmotionNameInput');
    const selectedEmotionValueInput = document.getElementById('selectedEmotionValueInput');
    const emotionIdInput = document.getElementById('emotionIdInput');

    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                const parentLabel = this.closest('label');
                const emotionName = parentLabel.getAttribute('title');
                const emotionValue = this.value;
                const emotionId = this.dataset.emotionid; // e.g., "4" for Disappointed

                // Update visible elements (if any)
                document.getElementById("selectedEmotionEmoji").src = `./images/emotions/${emotionValue}.svg`;
                document.getElementById("selectedEmotionName").innerText = emotionName;

                // Update the hidden inputs
                selectedEmotionNameInput.value = emotionName;
                selectedEmotionValueInput.value = emotionValue;
                emotionIdInput.value = emotionId;

                nextButtonContainer.classList.remove('hidden');
                console.log("Hidden input emotionId value:", emotionIdInput.value);
            }
        });
    });
});
