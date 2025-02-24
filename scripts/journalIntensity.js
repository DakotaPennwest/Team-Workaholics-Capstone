document.addEventListener('DOMContentLoaded', () => {
    // Try to get emotion data from URL
    const urlParams = new URLSearchParams(window.location.search);
    let emotionName = urlParams.get('emotionName') || 'excited'; // Default fallback to 'excited'
    const emotionValue = urlParams.get('emotionValue') || 'excited'; // Default fallback to 'excited'

    // Ensure emotionName is properly defined, just in case
    if (!emotionName) {
        emotionName = 'excited';
    }

    // Store the passed emotion data in hidden form fields (for further usage or form submission)
    document.getElementById('passedEmotionName').value = emotionName;
    document.getElementById('passedEmotionValue').value = emotionValue;

    // Update the question text with the passed emotion
    document.getElementById('emotionIntensityQuestion').innerHTML =
        `How <u>${emotionName.toLowerCase()}</u> do you feel?`;

    // Update the emoji display
    document.getElementById('selectedEmotionEmoji').src =
        `./images/emotions/${emotionValue}.svg`;

    // Cache DOM elements
    const sliderContainer = document.querySelector('.slider');
    const dragHandle = document.querySelector('.slider-handle');
    const progressBar = document.querySelector('.slider-progress');
    const snapPoints = [...document.querySelectorAll('.slider-stop')];

    // Hidden form fields for final submission
    const finalEmotionName = document.getElementById('finalEmotionName');
    const finalEmotionValue = document.getElementById('finalEmotionValue');
    const finalIntensityLevel = document.getElementById('finalIntensityLevel');
    const finalIntensityLabel = document.getElementById('finalIntensityLabel');

    // Define emotion intensity levels
    const emotionIntensityLevels = ['Barely', 'Somewhat', 'Moderately', 'Very', 'Extremely'];

    let isHandleDragging = false;

    function updateEmotionDisplay(intensityIndex) {
        const intensityLabel = emotionIntensityLevels[intensityIndex];

        // Update display elements
        document.getElementById('selectedIntensity').textContent = intensityLabel;
        document.getElementById('selectedEmotionName').textContent =
            `${intensityLabel} ${emotionName}`;
        document.getElementById('selectedIntensityBar').src =
            `./images/intensityBar/intensityBar${intensityIndex + 1}.svg`;

        // Update hidden form fields
        finalEmotionName.value = emotionName;
        finalEmotionValue.value = emotionValue;
        finalIntensityLevel.value = intensityIndex;
        finalIntensityLabel.value = intensityLabel;
    }

    function updateSliderVisuals(handlePosition) {
        dragHandle.style.left = `${handlePosition}px`;

        const firstSnapPointBounds = snapPoints[0].getBoundingClientRect();
        const sliderBounds = sliderContainer.getBoundingClientRect();
        const progressBarStart = firstSnapPointBounds.left - sliderBounds.left;

        progressBar.style.left = `${progressBarStart}px`;
        progressBar.style.width = `${handlePosition - progressBarStart}px`;
    }

    function snapHandleToPoint(snapPoint) {
        const snapPointBounds = snapPoint.getBoundingClientRect();
        const sliderBounds = sliderContainer.getBoundingClientRect();
        const snapPosition = snapPointBounds.left - sliderBounds.left;

        updateSliderVisuals(snapPosition);
        updateEmotionDisplay(snapPoints.indexOf(snapPoint));
    }

    function findNearestSnapPoint(currentPosition) {
        const sliderBounds = sliderContainer.getBoundingClientRect();

        return snapPoints.reduce((nearestIndex, snapPoint, index) => {
            const snapPointBounds = snapPoint.getBoundingClientRect();
            const snapPosition = snapPointBounds.left - sliderBounds.left;
            const distanceToCurrentPoint = Math.abs(currentPosition - snapPosition);
            const distanceToNearestPoint = Math.abs(currentPosition -
                (snapPoints[nearestIndex].getBoundingClientRect().left - sliderBounds.left));

            return distanceToCurrentPoint < distanceToNearestPoint ? index : nearestIndex;
        }, 0);
    }

    // Add click handlers to snap points
    snapPoints.forEach(snapPoint => {
        snapPoint.addEventListener('click', () => snapHandleToPoint(snapPoint));
    });

    // Handle drag start
    dragHandle.addEventListener('mousedown', () => {
        isHandleDragging = true;
    });

    // Handle dragging
    document.addEventListener('mousemove', (e) => {
        if (!isHandleDragging) return;

        const sliderBounds = sliderContainer.getBoundingClientRect();
        const leftmostSnapBounds = snapPoints[0].getBoundingClientRect();
        const rightmostSnapBounds = snapPoints[snapPoints.length - 1].getBoundingClientRect();

        const minPosition = leftmostSnapBounds.left - sliderBounds.left;
        const maxPosition = rightmostSnapBounds.left - sliderBounds.left;

        let newHandlePosition = e.clientX - sliderBounds.left;
        newHandlePosition = Math.max(minPosition, Math.min(newHandlePosition, maxPosition));

        updateSliderVisuals(newHandlePosition);
        updateEmotionDisplay(findNearestSnapPoint(newHandlePosition));
    });

    // Handle drag end
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

    // Form submission handler to save emotion intensity and redirect
    const intensityForm = document.getElementById('intensityForm');
    intensityForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const emotionName = finalEmotionName.value;
        const emotionValue = finalEmotionValue.value;
        const intensityLevel = finalIntensityLevel.value;
        const intensityLabel = finalIntensityLabel.value;

        fetch('saveIntensity.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `emotionName=${encodeURIComponent(emotionName)}&emotionValue=${encodeURIComponent(emotionValue)}&intensityLevel=${encodeURIComponent(intensityLevel)}&intensityLabel=${encodeURIComponent(intensityLabel)}`
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Redirect with emotion data
                    window.location.href = `journalJournaling.html?emotionName=${encodeURIComponent(emotionName)}`;
                } else {
                    console.error('Error saving emotion intensity:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
});
