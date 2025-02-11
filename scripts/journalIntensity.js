document.addEventListener("DOMContentLoaded", function() {
    // Grab all required elements.
    const stops = document.querySelectorAll('.slider-stop');
    const handle = document.querySelector('.slider-handle');
    const progress = document.querySelector('.slider-progress');
    const slider = document.querySelector('.slider');
  
    // Global offset (in pixels) to move the progress line's start position left of the first dot.
    const offset = 12;
  
    // This function updates the text in the UI based on which stop was selected.
    // It reads the data-stop attribute from the stop element to determine the intensity.
    function updateIntensityText(stopElement) {
      // Mapping from the stop value to an intensity adjective.
      const intensityMapping = {
        "1": "Barely",
        "2": "Somewhat",
        "3": "Moderately",
        "4": "Very",
        "5": "Extremely"
      };
      // Get the stop's intensity value (as a string).
      const intensityValue = stopElement.dataset.stop;
      // Look up the adjective (default to an empty string if not found).
      const adjective = intensityMapping[intensityValue] || "";
      
      // Update the two UI text elements:
      // 1. This element shows just the intensity.
      document.getElementById("selectedIntensity").innerText = adjective;
      // 2. This element shows both the intensity and the emotion (here, "Excited").
      // (This can be updated later to reflect the actual selected emotion.)
      document.getElementById("selectedEmotionName").innerText = adjective + " Excited";
      
      // Update the intensity bar image based on the selected stop.
      // It will load the image intensityBar1.svg, intensityBar2.svg, etc.
      document.getElementById("selectedIntensityBar").src = `./images/intensityBar/intensityBar${intensityValue}.svg`;
    }
  
    // Updates the slider when a stop is clicked.
    function updateSliderByStop(stop) {
      const sliderRect = slider.getBoundingClientRect();
      const stopRect = stop.getBoundingClientRect();
      const centerX = stopRect.left - sliderRect.left + stopRect.width / 2;
      handle.style.left = centerX + 'px';
  
      // Get the center position of the first stop.
      const firstStop = stops[0];
      const firstRect = firstStop.getBoundingClientRect();
      const firstCenter = firstRect.left - sliderRect.left + firstRect.width / 2;
  
      // Start the progress line offset pixels to the left of the first stop’s center.
      progress.style.left = (firstCenter - offset) + 'px';
      // Set the width so that it spans from the offset start to the selected stop’s center.
      progress.style.width = (centerX - (firstCenter - offset)) + 'px';
  
      // Update the UI text and image based on the selected stop.
      updateIntensityText(stop);
    }
  
    // Attach click events to each stop.
    stops.forEach(stop => {
      stop.addEventListener('click', function() {
        updateSliderByStop(this);
      });
    });
  
    // Variables for drag handling.
    let isDragging = false;
    let dragSliderRect = null;
    let stopsPositions = [];
  
    // When the user presses down on the handle, start the drag.
    handle.addEventListener('mousedown', function(e) {
      isDragging = true;
      dragSliderRect = slider.getBoundingClientRect();
      // Calculate and store the center positions for all stops relative to the slider.
      stopsPositions = [];
      stops.forEach(stop => {
        const stopRect = stop.getBoundingClientRect();
        const pos = stopRect.left - dragSliderRect.left + stopRect.width / 2;
        stopsPositions.push(pos);
      });
      e.preventDefault();
    });
  
    // While dragging, update the handle and progress line positions.
    document.addEventListener('mousemove', function(e) {
      if (!isDragging) return;
      let x = e.clientX - dragSliderRect.left;
      // Clamp x between the first and last stops.
      const minX = stopsPositions[0];
      const maxX = stopsPositions[stopsPositions.length - 1];
      if (x < minX) x = minX;
      if (x > maxX) x = maxX;
      handle.style.left = x + 'px';
  
      // Update the progress line with the offset.
      progress.style.left = (stopsPositions[0] - offset) + 'px';
      progress.style.width = (x - (stopsPositions[0] - offset)) + 'px';
    });
  
    // On mouseup, snap the handle to the nearest stop and update the progress line.
    document.addEventListener('mouseup', function(e) {
      if (!isDragging) return;
      isDragging = false;
      const handleRect = handle.getBoundingClientRect();
      const currentX = handleRect.left - dragSliderRect.left + handleRect.width / 2;
  
      // Determine the stop position closest to the handle's current center.
      let nearestPos = stopsPositions[0];
      let nearestIndex = 0;
      stopsPositions.forEach((pos, index) => {
        if (Math.abs(pos - currentX) < Math.abs(nearestPos - currentX)) {
          nearestPos = pos;
          nearestIndex = index;
        }
      });
  
      handle.style.left = nearestPos + 'px';
      progress.style.left = (stopsPositions[0] - offset) + 'px';
      progress.style.width = (nearestPos - (stopsPositions[0] - offset)) + 'px';
  
      // Update the UI text and image based on the snapped stop.
      updateIntensityText(stops[nearestIndex]);
    });
  
    // Initialize the slider at a default stop (for example, the third stop).
    updateSliderByStop(stops[2]);
  });
  