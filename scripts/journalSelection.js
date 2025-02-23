document.addEventListener("DOMContentLoaded", function() {
    const radioButtons = document.querySelectorAll('input[name="emotion"]');
    const nextButtonContainer = document.querySelector('.form-button-container');
    const emotionForm = document.getElementById('emotionForm');
    const selectedEmotionNameInput = document.getElementById('selectedEmotionNameInput');
    const selectedEmotionValueInput = document.getElementById('selectedEmotionValueInput');
  
    radioButtons.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                // Get the parent label of this radio input
                const parentLabel = this.closest('label');
                
                // Get emotion data
                const emotionName = parentLabel.getAttribute('title');
                const emotionValue = this.value;
                
                // Update the emoji image
                document.getElementById("selectedEmotionEmoji").src = `./images/emotions/${emotionValue}.svg`;
                
                // Update the emotion name text
                document.getElementById("selectedEmotionName").innerText = emotionName;
                
                // Update hidden form inputs
                selectedEmotionNameInput.value = emotionName;
                selectedEmotionValueInput.value = emotionValue;
                
                // Show the form container with Next button
                nextButtonContainer.classList.remove('hidden');
            }
        });
    });

    // Add form submission handler
    emotionForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Get the selected emotion data
        const emotionName = selectedEmotionNameInput.value;
        const emotionValue = selectedEmotionValueInput.value;
        
        // Redirect to the intensity page with emotion data as URL parameters
        window.location.href = `journalEmotionIntensity.html?emotionName=${encodeURIComponent(emotionName)}&emotionValue=${encodeURIComponent(emotionValue)}`;
    });
});