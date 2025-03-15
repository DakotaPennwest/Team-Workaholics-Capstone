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


// Note: I tried forever to see if I could simply export out the journal div as a png
// but nothing seemed to work, so I am resorting to just exporting as a simple txt file.
function setupDownloadTextFileButton() {
    const downloadIcon = document.getElementById("downloadIcon");
    if (!downloadIcon) {
        console.error("Download button (downloadIcon) not found.");
        return;
    }

    downloadIcon.addEventListener("click", function() {
        // Extract text from the page elements
        const authorName = document.getElementById("journalAuthorName")?.textContent.trim() || "";
        const journalNumberText = document.getElementById("journalNumber")?.textContent.trim() || "";
        const journalDate = document.getElementById("journalDate")?.textContent.trim() || "";
        const journalEmotion = document.getElementById("journalEmotion")?.textContent.trim() || "";
        const journalPrompt = document.getElementById("journalPrompt")?.textContent.trim() || "";
        const journalContent = document.getElementById("journalContent")?.textContent.trim() || "";

        // Format the text for export 
        // Note: I have to keep the text pushed against the left all the way or else it messed with formatting
        const textContent = 
`Journal Author name: ${authorName}
Journal Number: ${journalNumberText}
Journal Date: ${journalDate}

Emotion Chosen: ${journalEmotion}

Journal Prompt: ${journalPrompt}

Journal: ${journalContent}`;

        // Build the file name by removing spaces from journalNumberText.
        // For example, "Journal 40" becomes "Journal40.txt".
        const fileName =  journalNumberText.replace(/\s/g, "") + ".txt";

        // Create a Blob with the text content
        const blob = new Blob([textContent], { type: "text/plain" });
        const url = URL.createObjectURL(blob);

        // Create a temporary link element to trigger the download
        const link = document.createElement("a");
        link.href = url;
        link.download = fileName;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    });
}

// event listeners
window.addEventListener("load", adjustJournalBox);
window.addEventListener("load", setupDownloadTextFileButton);

