document.addEventListener("DOMContentLoaded", function() {
    // Check if we need to force reload
    if (window.localStorage && localStorage.getItem('reloadHomepage') === 'true') {
        localStorage.removeItem('reloadHomepage');
        // Force a reload from server, not cache
        window.location.reload(true);
        return;
    }
    
    // Fetch user info
    fetch('getUserInfo.php')
        .then(response => response.json())
        .then(data => {
            if (data.username) {
                document.getElementById('userFirstName').textContent = data.username;
            }
        })
        .catch(error => {
            console.error('Error fetching user info:', error);
        });
    
    // Fetch strategy info with cache-busting
    fetch('getStrategyInfo.php?_=' + new Date().getTime())
        .then(response => response.json())
        .then(data => {
            console.log("Received strategy data:", data); // Add debugging
            
            if (data.success) {
                // Update strategy name
                const currentStrategyElement = document.getElementById('currentStrategy');
                if (currentStrategyElement) {
                    currentStrategyElement.innerHTML = 'Your current strategy is<br><u>' + 
                        (data.strategy_name || 'Not assigned') + '</u>';
                }
                
                // Update journals until next strategy
                const journalsUntilNext = document.getElementById('journalsUntilNextStrategy');
                if (journalsUntilNext) {
                    journalsUntilNext.textContent = data.entries_until_next;
                }
            } else {
                console.error('Strategy data error:', data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching strategy info:', error);
        });
});
