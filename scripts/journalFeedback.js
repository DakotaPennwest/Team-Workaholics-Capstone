document.addEventListener("DOMContentLoaded", function() {
    // Select all radio inputs with the name "feedback"
    const feedbackRadios = document.querySelectorAll('input[name="feedback"]');
    
    feedbackRadios.forEach(radio => {
      radio.addEventListener('change', function() {
        if (this.checked) {
          // Find the closest parent label
          const feedbackLabel = this.closest('label');
          // Get the value to display from the label's title attribute (or you could use the text from .feedback-text)
          const ratingText = feedbackLabel.getAttribute('title');
          // Update the element with id "selectedFeedbackRating"
          document.getElementById('selectedFeedbackRating').textContent = ratingText;
        }
      });
    });
  });
  