document.addEventListener('DOMContentLoaded', () => {
    // Read emotion data from hidden inputs (set by PHP)
    const emotionName = document.getElementById('passedEmotionName').value || 'excited';
    const emotionValue = document.getElementById('passedEmotionValue').value || 'excited';

    // Update the question text and the emoji display using these values
    document.getElementById('emotionIntensityQuestion').innerHTML =
        `How <u>${emotionName.toLowerCase()}</u> do you feel?`;
    document.getElementById('selectedEmotionEmoji').src =
        `./images/emotions/${emotionValue}.svg`;

    // Cache DOM elements for the slider and final form fields
    const sliderContainer = document.querySelector('.slider');
    const dragHandle = document.querySelector('.slider-handle');
    const progressBar = document.querySelector('.slider-progress');
    const snapPoints = [...document.querySelectorAll('.slider-stop')];
    const finalEmotionName = document.getElementById('finalEmotionName');
    const finalEmotionValue = document.getElementById('finalEmotionValue');
    const finalIntensityLevel = document.getElementById('finalIntensityLevel');
    const finalIntensityLabel = document.getElementById('finalIntensityLabel');
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

        //const progressBarStart = firstSnapPointBounds.left - sliderBounds.left;
            // I commented this line out to preserve what it was before - Dakota

        // Offset by 5px
        // We do this because the progress bar start needs to be just a bit before the first stop
        // That way the bar maintains a visual margin around the stop and looks much nicer.
        // I have it as a const just so I can easily find it again in case I need to adjust it - Dakota
        const offset = 5;
        const progressBarStart = (firstSnapPointBounds.left - sliderBounds.left) - offset;

        progressBar.style.left = `${progressBarStart}px`;
        progressBar.style.width = `${handlePosition - progressBarStart}px`;
    }

    function snapHandleToPoint(snapPoint) {
        const snapPointBounds = snapPoint.getBoundingClientRect();
        const sliderBounds = sliderContainer.getBoundingClientRect();

        //const snapPosition = snapPointBounds.left - sliderBounds.left;
            // I commented this line out to preserve what it was before - Dakota

        // Add half the stop's width to get the center
        // We do this to better center the slide progress dot overtop the stops - Dakota
        const snapPosition = (snapPointBounds.left - sliderBounds.left) + snapPointBounds.width / 2;

        updateSliderVisuals(snapPosition);
        updateEmotionDisplay(snapPoints.indexOf(snapPoint));
    }

    function findNearestSnapPoint(currentPosition) {
        const sliderBounds = sliderContainer.getBoundingClientRect();

        return snapPoints.reduce((nearestIndex, snapPoint, index) => {
            const snapPointBounds = snapPoint.getBoundingClientRect();

            /* Commented this out to preserve what was here - Dakota
            const snapPosition = snapPointBounds.left - sliderBounds.left;
            const distanceToCurrentPoint = Math.abs(currentPosition - snapPosition);
            const distanceToNearestPoint = Math.abs(currentPosition -
                (snapPoints[nearestIndex].getBoundingClientRect().left - sliderBounds.left));
            */

             // Center of that stop:
             // All of this is to try and center on the snapping points better - Dakota
            const snapCenter = (snapPointBounds.left - sliderBounds.left) +  snapPointBounds.width / 2;
            const distanceToCurrentPoint = Math.abs(currentPosition - snapCenter);

            const nearestSnapBounds = snapPoints[nearestIndex].getBoundingClientRect();
            const nearestSnapCenter = (nearestSnapBounds.left - sliderBounds.left) +  nearestSnapBounds.width / 2;
            const distanceToNearestPoint = Math.abs(currentPosition - nearestSnapCenter);


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

        //const minPosition = leftmostSnapBounds.left - sliderBounds.left;
        //const maxPosition = rightmostSnapBounds.left - sliderBounds.left;
            // Commented out to preserve what was here before - Dakota

        // Min & max should be the centers of the leftmost and rightmost stops
        // We do this to better center the slide progress dot overtop the stops - Dakota
        const minPosition = (leftmostSnapBounds.left - sliderBounds.left)  +  leftmostSnapBounds.width / 2;
        const maxPosition = (rightmostSnapBounds.left - sliderBounds.left) +  rightmostSnapBounds.width / 2;

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
                    // Redirect after successful save
                    window.location.href = `journalJournaling.php?emotionName=${encodeURIComponent(emotionName)}`;
                } else {
                    console.error('Error saving emotion intensity:', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
    });
});