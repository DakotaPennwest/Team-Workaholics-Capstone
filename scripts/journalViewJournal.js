


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
window.addEventListener("load", setupDownloadTextFileButton);

