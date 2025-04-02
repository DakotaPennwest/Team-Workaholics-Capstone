document.addEventListener("DOMContentLoaded", function() {
    // Attach click event listener to the download button
    var downloadBtn = document.getElementById("downloadIcon");
    if (downloadBtn) {
      downloadBtn.addEventListener("click", downloadTable);
    }
  
    function downloadTable() {
      // Locate the table element on the page
      var table = document.querySelector("table");
      if (!table) {
        console.error("Table element not found.");
        return;
      }
  
      // Start building the text output with the title "Strategy Report"
      var textContent = "Strategy Report\n\n";
  
      // Get header text from the table head
      var headerCells = table.querySelectorAll("thead th");
      var headers = Array.from(headerCells).map(th => th.textContent.trim());
  
      // Combine the header row and each body row into a 2D array
      var rows = [];
      rows.push(headers);
  
      // Get each row in the table body
      var bodyRows = table.querySelectorAll("tbody tr");
      bodyRows.forEach(tr => {
        var cells = tr.querySelectorAll("td");
        if (cells.length > 0) {
          var rowData = Array.from(cells).map(td => td.textContent.trim());
          rows.push(rowData);
        }
      });
  
      // Determine the maximum width for each column for proper alignment
      var colCount = headers.length;
      var colWidths = new Array(colCount).fill(0);
      rows.forEach(row => {
        row.forEach((cell, index) => {
          colWidths[index] = Math.max(colWidths[index], cell.length);
        });
      });
  
      // Format each row as a text line with padded columns
      rows.forEach((row, rowIndex) => {
        var rowString = row.map((cell, index) => cell.padEnd(colWidths[index] + 2, " ")).join("| ");
        textContent += rowString.trimEnd() + "\n";
  
        // After the header row, add a separator line
        if (rowIndex === 0) {
          var separator = colWidths.map(width => "".padEnd(width + 2, "-")).join("+");
          textContent += separator + "\n";
        }
      });
  
      // Create a Blob from the text and generate a temporary download URL
      var blob = new Blob([textContent], { type: "text/plain" });
      var url = URL.createObjectURL(blob);
  
      // Create a temporary anchor element to trigger the download
      var a = document.createElement("a");
      a.href = url;
      a.download = "strategy_report.txt";
      document.body.appendChild(a);
      a.click();
  
      // Clean up by removing the anchor and revoking the URL
      document.body.removeChild(a);
      URL.revokeObjectURL(url);
    }
  });
  