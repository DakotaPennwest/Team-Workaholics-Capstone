document.addEventListener("DOMContentLoaded", function() {
    // Select all radio inputs with the name "emotion"
    const radioButtons = document.querySelectorAll('input[name="emotion"]');
    // Cache the container for the Next button
    const nextButtonContainer = document.querySelector('.form-button-container');
  
    radioButtons.forEach(radio => {
      radio.addEventListener('change', function() {
        if (this.checked) {
          // Get the parent label of this radio input
          const parentLabel = this.closest('label');
          
          // Use the label's title attribute as the emotion name
          const emotionName = parentLabel.getAttribute('title');
          
          // Update the emoji image source based on the radio value.
          // Assumes images are stored at "./images/emotions/{value}.svg"
          document.getElementById("selectedEmotionEmoji").src = `./images/emotions/${this.value}.svg`;
          
          // Update the emotion name text.
          document.getElementById("selectedEmotionName").innerText = emotionName;
          
          // Remove the hidden class to reveal the Next button container
          nextButtonContainer.classList.remove('hidden');
        }
      });
    });
  });
  