// Custom slider implementation for selecting emotion intensity levels.

document.addEventListener('DOMContentLoaded', () => {
  // Cache DOM element references
  const sliderContainer = document.querySelector('.slider');
  const dragHandle = document.querySelector('.slider-handle');
  const progressBar = document.querySelector('.slider-progress');
  const snapPoints = [...document.querySelectorAll('.slider-stop')];
  
  // Define emotion intensity levels from lowest to highest
  const emotionIntensityLevels = ['Barely', 'Somewhat', 'Moderately', 'Very', 'Extremely'];
  
  // Track whether user is currently dragging the handle
  let isHandleDragging = false;

  // Updates the emotion display text and intensity bar image
  function updateEmotionDisplay(intensityIndex) {
    const intensityLabel = emotionIntensityLevels[intensityIndex];
    document.getElementById('selectedIntensity').textContent = intensityLabel;
    document.getElementById('selectedEmotionName').textContent = `${intensityLabel} Excited`;
    document.getElementById('selectedIntensityBar').src = `./images/intensityBar/intensityBar${intensityIndex + 1}.svg`;
  }

  // Updates the visual elements of the slider (handle and progress bar)
  function updateSliderVisuals(handlePosition) {
    dragHandle.style.left = `${handlePosition}px`;
    
    // Get the leftmost snap point position for the progress bar
    const firstSnapPointBounds = snapPoints[0].getBoundingClientRect();
    const sliderBounds = sliderContainer.getBoundingClientRect();
    const progressBarStart = firstSnapPointBounds.left - sliderBounds.left;
    
    progressBar.style.left = `${progressBarStart}px`;
    progressBar.style.width = `${handlePosition - progressBarStart}px`;
  }

  // Moves the slider handle to a specific snap point
  function snapHandleToPoint(snapPoint) {
    const snapPointBounds = snapPoint.getBoundingClientRect();
    const sliderBounds = sliderContainer.getBoundingClientRect();
    const snapPosition = snapPointBounds.left - sliderBounds.left;
    
    updateSliderVisuals(snapPosition);
    updateEmotionDisplay(snapPoints.indexOf(snapPoint));
  }

  // Determines which snap point is closest to the current handle position
  function findNearestSnapPoint(currentPosition) {
    const sliderBounds = sliderContainer.getBoundingClientRect();
    
    return snapPoints.reduce((nearestIndex, snapPoint, index) => {
      const snapPointBounds = snapPoint.getBoundingClientRect();
      const snapPosition = snapPointBounds.left - sliderBounds.left;
      const distanceToCurrentPoint = Math.abs(currentPosition - snapPosition);
      const distanceToNearestPoint = Math.abs(currentPosition - (snapPoints[nearestIndex].getBoundingClientRect().left - sliderBounds.left));
      
      return distanceToCurrentPoint < distanceToNearestPoint ? index : nearestIndex;
    }, 0);
  }

  // Add click handlers to each snap point for direct selection
  snapPoints.forEach(snapPoint => {
    snapPoint.addEventListener('click', () => snapHandleToPoint(snapPoint));
  });

  // Start dragging when mouse is pressed on handle
  dragHandle.addEventListener('mousedown', () => {
    isHandleDragging = true;
  });

  // Update handle position while dragging
  document.addEventListener('mousemove', (e) => {
    if (!isHandleDragging) return;
    
    // Calculate the valid range for handle movement
    const sliderBounds = sliderContainer.getBoundingClientRect();
    const leftmostSnapBounds = snapPoints[0].getBoundingClientRect();
    const rightmostSnapBounds = snapPoints[snapPoints.length - 1].getBoundingClientRect();
    
    const minPosition = leftmostSnapBounds.left - sliderBounds.left;
    const maxPosition = rightmostSnapBounds.left - sliderBounds.left;
    
    // Keep handle within the valid range
    let newHandlePosition = e.clientX - sliderBounds.left;
    newHandlePosition = Math.max(minPosition, Math.min(newHandlePosition, maxPosition));
    
    updateSliderVisuals(newHandlePosition);
    updateEmotionDisplay(findNearestSnapPoint(newHandlePosition));
  });

  // Snap to nearest point when dragging ends
  document.addEventListener('mouseup', () => {
    if (!isHandleDragging) return;
    
    isHandleDragging = false;
    const handleBounds = dragHandle.getBoundingClientRect();
    const sliderBounds = sliderContainer.getBoundingClientRect();
    const currentPosition = handleBounds.left - sliderBounds.left;
    
    const nearestSnapIndex = findNearestSnapPoint(currentPosition);
    snapHandleToPoint(snapPoints[nearestSnapIndex]);
  });

  // Initialize slider at "Moderately" (middle position)
  snapHandleToPoint(snapPoints[2]);
});
