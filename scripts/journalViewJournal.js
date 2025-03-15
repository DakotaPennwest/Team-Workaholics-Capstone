function adjustJournalBox() {
    const viewJournalBox = document.querySelector('.view-journal-box');
    const journalInfo = document.querySelector('.journal-info-container');
    const journalPrompt = document.querySelector('.journal-prompt');
    const journalContent = document.querySelector('.journal-content');

    // Calculate the total height needed for the content
    const totalHeight = journalInfo.offsetHeight + journalPrompt.offsetHeight + journalContent.offsetHeight;
    const newHeight = Math.max(450, totalHeight);
    viewJournalBox.style.height = newHeight + "px";

    // Below is code that should scale the size of the journal box to fit inside the screen
    // This should make it so even if a journal is really long, it should fit on the screen
    // Please note I am not sure about this code and haven't extensively tested it, so we can remove it if it causes issues
    
    const viewportHeight = window.innerHeight;
    let scale = 1;
    if (newHeight > viewportHeight * 0.7) {
        scale = (viewportHeight * 0.7) / newHeight;
    }
    viewJournalBox.style.transform = `scale(${scale})`;
    viewJournalBox.style.transformOrigin = "center";
}

// event listener that called the resizing function
window.addEventListener("load", adjustJournalBox);

