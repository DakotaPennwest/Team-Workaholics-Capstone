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
    
    // Check primary emotion counts on page load
    const primaryButtons = document.querySelectorAll('.emotion-option.main input[type="radio"]');
    
    function checkPrimaryEmotion(button) {
        const emotionId = button.dataset.emotionid;
        const emotionValue = button.value; // Get the emotion value (happy, sad, etc.)
        
        console.log("Checking emotion ID:", emotionId, "Value:", emotionValue);
        
        fetch(`getEmotionCount.php?emotion=${emotionId}`)
            .then(response => {
                if (!response.ok) {
                    throw new Error("Network response was not ok");
                }
                return response.json();
            })
            .then(data => {
                console.log("Fetched Data for emotion ID " + emotionId + ":", data);
                
                const parentColumn = button.closest('.emotion-column');
                const secondaryEmotions = parentColumn.querySelectorAll('.emotion-option.secondary');
                
                // Show or hide secondary emotions based on the count
                if (data.count >= 5) {
                    console.log("Showing secondary emotions for:", emotionValue);
                    secondaryEmotions.forEach(emotion => {
                        emotion.style.display = 'flex';
                    });
                } else {
                    console.log("Hiding secondary emotions for:", emotionValue);
                    secondaryEmotions.forEach(emotion => {
                        emotion.style.display = 'none';
                    });
                }
            })
            .catch(error => {
                console.error("Error fetching emotion count:", error);
            });
    }
    
    // Check all primary emotions on page load
    primaryButtons.forEach(function(button) {
        checkPrimaryEmotion(button);
        
        // Add event listeners to primary buttons
        button.addEventListener('change', function() {
            if (this.checked) {
                checkPrimaryEmotion(this);
            }
        });
    });
});
