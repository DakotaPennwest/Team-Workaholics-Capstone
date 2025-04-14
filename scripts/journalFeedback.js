document.addEventListener("DOMContentLoaded", function() {
  const feedbackRadios = document.querySelectorAll('input[name="feedback"]');
  feedbackRadios.forEach(radio => {
    radio.addEventListener('change', function() {
      if (this.checked) {
        const feedbackLabel = this.closest('label');
        const ratingText = feedbackLabel.getAttribute('title');
        const ratingEl = document.getElementById('selectedFeedbackRating');
        if (ratingEl) {
          ratingEl.textContent = ratingText;
        }
        // Store the selected feedback in the hidden input
          const feedbackValueInput = document.getElementById('selectedEmotionValueInput'); 
          feedbackValueInput.value = this.value; // 'helpful' or 'unhelpful'
        
        // Unhide the next button container
        const nextButtonContainer = document.querySelector('.form-button-container');
        if (nextButtonContainer) {
          nextButtonContainer.classList.remove('hidden');
        }
      }
    });
  });

  // Fetch the assigned coping strategy from the server
  fetch('getAssignedStrategy.php')
    .then(response => response.json())
    .then(json => {
      if (!json.success) throw new Error(json.message);
      
      // Expecting the JSON to include strategy_name, strategy_descript, and strategy_image_url, and assignment_id
      const { assignment_id, strategy_name, strategy_descript, strategy_image_url } = json.data;
      
      // Update the elements
      const strategyNameEl = document.getElementById('strategyName');
      const strategyDescriptionEl = document.getElementById('strategyDescription');
      const strategyImageEl = document.getElementById('strategyImage');
      const assignedMessageEl = document.getElementById('assignedStrategyMessage');
      const assignedStrategyNameEl = document.getElementById('assignedStrategyName');
      const assignedStrategyNameFeedbackEl = document.getElementById('assignedStrategyNameFeedback');
      const assignedStrategyNameFeedbackEl2 = document.getElementById('assignedStrategyNameFeedback2');

      if (strategyNameEl) {
        strategyNameEl.textContent = strategy_name;
      }
      if (strategyDescriptionEl) {
        strategyDescriptionEl.textContent = strategy_descript;
      }
      if (strategyImageEl) {
        strategyImageEl.src = strategy_image_url;
      }
      if (assignedMessageEl) {
        assignedMessageEl.innerHTML = `Your assigned strategy is <u>${strategy_name}</u>`;
      }
      if (assignedStrategyNameEl) {
        assignedStrategyNameEl.textContent = strategy_name;
      }
      if (assignedStrategyNameFeedbackEl) {
        assignedStrategyNameFeedbackEl.textContent = strategy_name;
      }
      if (assignedStrategyNameFeedbackEl2) {
        assignedStrategyNameFeedbackEl2.textContent = strategy_name;
      }
      // Capture assignment ID to send with feedback
            if (assignmentIdInput) {
                assignmentIdInput.value = assignment_id; // Set assignment ID from server response
            }
    })
    .catch(err => {
      console.error('Error fetching assigned strategy:', err);
      const assignedMessageEl = document.getElementById('assignedStrategyMessage');
      if (assignedMessageEl) {
        assignedMessageEl.textContent = 'Sorry — no coping strategy available right now.';
      }
    });
});
