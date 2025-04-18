document.addEventListener("DOMContentLoaded", function() {
    const feedbackRadios = document.querySelectorAll('input[name="feedback"]');
    const nextButtonContainer = document.querySelector('.form-button-container');
    const assignmentIdInput = document.getElementById('assignmentIdInput');
    const feedbackForm = document.getElementById('emotionForm');
    const selectedEmotionValueInput = document.getElementById('selectedEmotionValueInput');
    const selectedEmotionNameInput = document.getElementById('selectedEmotionNameInput');
    const selectedFeedbackRating = document.getElementById('selectedFeedbackRating');
    
    // Update the form action to use submitFeedback.php
    if (feedbackForm) {
        feedbackForm.action = 'submitFeedback.php';
    }
    
    // Handle feedback selection
    feedbackRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.checked) {
                const feedbackLabel = this.closest('label');
                const ratingText = feedbackLabel.getAttribute('title');
                
                // Update the display
                if (selectedFeedbackRating) {
                    selectedFeedbackRating.textContent = ratingText;
                }
                
                // Store the selected feedback
                if (selectedEmotionValueInput) {
                    selectedEmotionValueInput.value = this.value;
                }
                
                if (selectedEmotionNameInput) {
                    selectedEmotionNameInput.value = ratingText;
                }
                
                // Show the next button
                if (nextButtonContainer) {
                    nextButtonContainer.classList.remove('hidden');
                }
            }
        });
    });
    
    // Get strategy info from the session
    fetch('getSessionStrategyInfo.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update strategy name in the UI
                const strategyName = data.strategy_name;
                const assignmentId = data.assignment_id;
                
                // Update the various strategy name elements
                const strategyElements = [
                    document.getElementById('assignedStrategyName'),
                    document.getElementById('assignedStrategyNameFeedback'),
                    document.getElementById('assignedStrategyNameFeedback2')
                ];
                
                strategyElements.forEach(element => {
                    if (element) {
                        element.textContent = strategyName;
                    }
                });
                
                // Update the message
                const messageElement = document.getElementById('assignedStrategyMessage');
                if (messageElement) {
                    messageElement.innerHTML = `Your assigned strategy is <u>${strategyName}</u>`;
                }
                
                // Set the assignment ID for feedback
                if (assignmentIdInput) {
                    assignmentIdInput.value = assignmentId;
                    console.log("Setting assignment ID:", assignmentId);
                }
            } else {
                console.error('Error loading strategy information:', data.message);
            }
        })
        .catch(err => {
            console.error('Error fetching strategy info:', err);
        });
});
