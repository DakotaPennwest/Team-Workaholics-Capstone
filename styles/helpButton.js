document.addEventListener('DOMContentLoaded', () => {
  // Create or get the help button.
  const helpButton = document.getElementById('helpButton') || createHelpButton();

  // Add event listener for help button that listens for user to click it
  helpButton.addEventListener('click', openHelpModal);

  // We do this because if we kept the transition on at all times, it would gltich the button when zooming
  helpButton.addEventListener('mouseleave', () => {
    helpButton.style.transition = "width 0.3s ease, transform 0.1s ease";
    setTimeout(() => helpButton.style.transition = "", 350);
  });
});

// Create the help button if it doesn't exist.
function createHelpButton() {
  const button = document.createElement('div');
  button.id = 'helpButton';
  button.innerHTML = `<span class="short">?</span>
                      <span class="long">Click here for help</span>`;
  document.body.appendChild(button);
  return button;
}

// Preload images until one fails; return the count.
// We do this so we can figure out how many images are part of the manual for this page
function preloadImageCount(folder) {
  return new Promise(resolve => {
    let count = 0;
    function checkNext() {
      const testImg = new Image();
      testImg.onload = () => { count++; checkNext(); };
      testImg.onerror = () => resolve(count);
      testImg.src = `${folder}${count + 1}.png`;
    }
    checkNext();
  });
}

// Open the help modal.
async function openHelpModal() {

  // We get the name of the current page from the url so we know what the name of the folder is
  const currentFile = window.location.pathname.split('/').pop().split('.')[0];

  // We use the name we got to find the right folder that holds the manual images for current page
  const imageFolder = `./images/helpPictures/${currentFile}/`;

  // All help Steps start at 1
  let currentStep = 1;

  // Check how many steps are in total
  const totalSteps = await preloadImageCount(imageFolder);

  // Create the modal and query its elements.
  const modal = createModal();
  const imageContainer = modal.querySelector('.help-modal-image');
  const dotContainer = modal.querySelector('.help-modal-dots');
  const closeButton = modal.querySelector('.help-modal-close');
  const prevButton = modal.querySelector('.help-modal-prev');
  const nextButton = modal.querySelector('.help-modal-next');

  // Dynamically add dots based on total steps.
  for (let i = 1; i <= totalSteps; i++) {
    const dot = document.createElement('span');
    dot.className = 'help-modal-dot';
    if (i === currentStep) dot.classList.add('active');
    dotContainer.appendChild(dot);
  }

  // Update the modal image.
  updateImage();

  // Attach event listeners.
  closeButton.addEventListener('click', closeModal);
  modal.addEventListener('click', e => { if (e.target === modal) closeModal(); });
  prevButton.addEventListener('click', e => {
    e.stopPropagation();
    if (currentStep > 1) { currentStep--; updateImage(); }
  });
  nextButton.addEventListener('click', e => {
    e.stopPropagation();
    if (currentStep < totalSteps) { currentStep++; updateImage(); }
  });

  document.body.appendChild(modal);

  // Update image and dots.
  function updateImage() {

    // We update the image as a background image rather than just an img source because
    // doing it this way lets me add our box shadow shading to the image which looks much nicer
    imageContainer.style.backgroundImage = `url(${imageFolder}${currentStep}.png)`;
    imageContainer.setAttribute('aria-label', `Help Step ${currentStep}`);
    Array.from(dotContainer.children).forEach((dot, index) => {
      dot.classList.toggle('active', index === currentStep - 1);
    });
  }

  function closeModal() {
    modal.remove();
  }
}

// Create the modal HTML structure.
function createModal() {
  const modal = document.createElement('div');
  modal.id = 'helpModal';
  modal.className = 'help-modal';
  modal.innerHTML = `
    <div class="help-modal-content">

      <div class="help-modal-image"></div>

      <div class="help-modal-dots"></div>

      <div class="help-modal-nav">

        <span class="help-modal-prev">
          <img src="./images/icons/leftArrow.svg" alt="Previous">
        </span>

        <span class="help-modal-instructions">Use the arrows to navigate</span>

        <span class="help-modal-next">
          <img src="./images/icons/rightArrow.svg" alt="Next">
        </span>

      </div>
      
      <span class="help-modal-close">&times;</span>

    </div>
  `;
  return modal;
}

