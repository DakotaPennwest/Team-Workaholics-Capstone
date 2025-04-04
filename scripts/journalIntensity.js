document.addEventListener('DOMContentLoaded', () => {
  // Retrieve emotion data from hidden inputs (set by PHP)
  const emotionName = document.getElementById('passedEmotionName').value || 'excited';
  const emotionValue = document.getElementById('passedEmotionValue').value || 'excited';

  // Update the question text and emoji display
  document.getElementById('emotionIntensityQuestion').innerHTML = 
    `How <u>${emotionName.toLowerCase()}</u> do you feel?`;
  document.getElementById('selectedEmotionEmoji').src = `./images/emotions/${emotionValue}.svg`;

  // Cache DOM elements for slider and final form fields
  const sliderContainer = document.querySelector('.slider');
  const dragHandle = document.querySelector('.slider-handle');
  const progressBar = document.querySelector('.slider-progress');
  const snapPoints = Array.from(document.querySelectorAll('.slider-stop'));
  const finalEmotionName = document.getElementById('finalEmotionName');
  const finalEmotionValue = document.getElementById('finalEmotionValue');
  const finalIntensityLevel = document.getElementById('finalIntensityLevel');
  const finalIntensityLabel = document.getElementById('finalIntensityLabel');
  const emotionIntensityLevels = ['Barely', 'Somewhat', 'Moderately', 'Very', 'Extremely'];
  let isHandleDragging = false;

  // Update display and hidden fields based on slider position
  function updateEmotionDisplay(intensityIndex) {
    const intensityLabel = emotionIntensityLevels[intensityIndex];
    document.getElementById('selectedIntensity').textContent = intensityLabel;
    document.getElementById('selectedEmotionName').textContent = `${intensityLabel} ${emotionName}`;
    document.getElementById('selectedIntensityBar').src = `./images/intensityBar/intensityBar${intensityIndex + 1}.svg`;
    finalEmotionName.value = emotionName;
    finalEmotionValue.value = emotionValue;
    finalIntensityLevel.value = intensityIndex;
    finalIntensityLabel.value = intensityLabel;
  }

  // Update the progress bar and handle position
  function updateSliderVisuals(handlePosition) {
    dragHandle.style.left = `${handlePosition}px`;
    const firstSnapBounds = snapPoints[0].getBoundingClientRect();
    const sliderBounds = sliderContainer.getBoundingClientRect();
    // Calculate the center of the first snap point for a better visual offset
    const progressBarStart = (firstSnapBounds.left - sliderBounds.left) + (firstSnapBounds.width / 2);
    progressBar.style.left = `${progressBarStart}px`;
    progressBar.style.width = `${handlePosition - progressBarStart}px`;
  }

  // Move handle to the center of the snap point and update display
  function snapHandleToPoint(snapPoint) {
    const snapBounds = snapPoint.getBoundingClientRect();
    const sliderBounds = sliderContainer.getBoundingClientRect();
    const snapPosition = (snapBounds.left - sliderBounds.left) + (snapBounds.width / 2);
    updateSliderVisuals(snapPosition);
    updateEmotionDisplay(snapPoints.indexOf(snapPoint));
  }

  // Find the nearest snap point based on the current position
  function findNearestSnapPoint(currentPosition) {
    const sliderBounds = sliderContainer.getBoundingClientRect();
    return snapPoints.reduce((nearestIndex, snapPoint, index) => {
      const snapBounds = snapPoint.getBoundingClientRect();
      const snapCenter = (snapBounds.left - sliderBounds.left) + (snapBounds.width / 2);
      const currentDistance = Math.abs(currentPosition - snapCenter);
      const nearestBounds = snapPoints[nearestIndex].getBoundingClientRect();
      const nearestCenter = (nearestBounds.left - sliderBounds.left) + (nearestBounds.width / 2);
      const nearestDistance = Math.abs(currentPosition - nearestCenter);
      return currentDistance < nearestDistance ? index : nearestIndex;
    }, 0);
  }

  // Attach click handlers to each snap point
  snapPoints.forEach(snapPoint => {
    snapPoint.addEventListener('click', () => snapHandleToPoint(snapPoint));
  });

  // Handle dragging of the slider handle
  dragHandle.addEventListener('mousedown', () => {
    isHandleDragging = true;
  });

  document.addEventListener('mousemove', (e) => {
    if (!isHandleDragging) return;
    const sliderBounds = sliderContainer.getBoundingClientRect();
    const leftSnapBounds = snapPoints[0].getBoundingClientRect();
    const rightSnapBounds = snapPoints[snapPoints.length - 1].getBoundingClientRect();
    const minPosition = (leftSnapBounds.left - sliderBounds.left) + (leftSnapBounds.width / 2);
    const maxPosition = (rightSnapBounds.left - sliderBounds.left) + (rightSnapBounds.width / 2);
    let newHandlePosition = e.clientX - sliderBounds.left;
    newHandlePosition = Math.max(minPosition, Math.min(newHandlePosition, maxPosition));
    updateSliderVisuals(newHandlePosition);
    updateEmotionDisplay(findNearestSnapPoint(newHandlePosition));
  });

  document.addEventListener('mouseup', () => {
    if (!isHandleDragging) return;
    isHandleDragging = false;
    const sliderBounds = sliderContainer.getBoundingClientRect();
    const handleBounds = dragHandle.getBoundingClientRect();
    const currentPosition = handleBounds.left - sliderBounds.left;
    const nearestSnapIndex = findNearestSnapPoint(currentPosition);
    snapHandleToPoint(snapPoints[nearestSnapIndex]);
  });

  // Initialize slider at the third snap point (middle)
  snapHandleToPoint(snapPoints[2]);

  // Handle form submission to save intensity and redirect
  const intensityForm = document.getElementById('intensityForm');
  intensityForm.addEventListener('submit', function(e) {
    e.preventDefault();
    const formEmotionName = finalEmotionName.value;
    const formEmotionValue = finalEmotionValue.value;
    const intensityLevel = finalIntensityLevel.value;
    const intensityLabel = finalIntensityLabel.value;
    fetch('saveIntensity.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: `emotionName=${encodeURIComponent(formEmotionName)}&emotionValue=${encodeURIComponent(formEmotionValue)}&intensityLevel=${encodeURIComponent(intensityLevel)}&intensityLabel=${encodeURIComponent(intensityLabel)}`
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.location.href = `journalJournaling.php?emotionName=${encodeURIComponent(formEmotionName)}`;
      } else {
        console.error('Error saving emotion intensity:', data.message);
      }
    })
    .catch(error => {
      console.error('Error:', error);
    });
  });
});
