document.addEventListener("DOMContentLoaded", function() {
    // Handle emotion selection
    const radioButtons = document.querySelectorAll('input[name="emotion"]');
    const nextButtonContainer = document.querySelector('.form-button-container');
    const selectedEmotionNameInput = document.getElementById('selectedEmotionNameInput');
    const selectedEmotionValueInput = document.getElementById('selectedEmotionValueInput');
    const emotionIdInput = document.getElementById('emotionIdInput');

    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                const parentLabel = this.closest('label');
                const emotionName = parentLabel.getAttribute('title');
                const emotionValue = this.value;
                const emotionId = this.dataset.emotionid;

                // Update UI elements
                document.getElementById("selectedEmotionEmoji").src = `./images/emotions/${emotionValue}.svg`;
                document.getElementById("selectedEmotionName").innerText = emotionName;

                // Update hidden inputs
                selectedEmotionNameInput.value = emotionName;
                selectedEmotionValueInput.value = emotionValue;
                emotionIdInput.value = emotionId;

                nextButtonContainer.classList.remove('hidden');
                console.log("Hidden input emotionId value:", emotionIdInput.value);
            }
        });
    });

    // Check primary emotion counts on page load and on change
    const primaryButtons = document.querySelectorAll('.emotion-option.main input[type="radio"]');

    // Function to check and update a primary emotion column
    function checkPrimaryEmotion(button) {
        const emotionValue = button.value;
        fetch(`getEmotionCount.php?emotion=${emotionValue}`)
            .then(response => response.json())
            .then(data => {
                if (data.count >= 5) {
                    const parentColumn = button.closest('.emotion-column');
                    parentColumn.classList.remove('hide-secondary');
                }
            })
            .catch(error => {
                console.error('Error fetching emotion count:', error);
            });
    }

    // Check for each primary emotion on page load
    primaryButtons.forEach(function(button) {
        checkPrimaryEmotion(button);
        // Also add the change event in case the user makes a new selection
        button.addEventListener('change', function() {
            checkPrimaryEmotion(button);
        });
    });
});
