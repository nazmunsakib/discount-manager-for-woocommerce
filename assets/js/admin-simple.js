/**
 * Simple Admin Interface - Debug Version
 */
(function() {
    console.log('DMWOO Simple: Loading...');
    
    function createSimpleInterface() {
        const container = document.getElementById('dmwoo-admin-root');
        if (!container) return;
        
        container.innerHTML = `
            <div style="padding: 20px;">
                <h2>Discount Rules</h2>
                <button id="add-rule-btn" class="button button-primary">Add New Rule</button>
                <div id="rules-container" style="margin-top: 20px;">
                    <p>Loading rules...</p>
                </div>
            </div>
        `;
        
        loadAndDisplayRules();
    }
    
    async function loadAndDisplayRules() {
        const container = document.getElementById('rules-container');
        if (!container) return;
        
        try {
            const response = await wp.apiFetch({
                path: '/dmwoo/v1/rules'
            });
            
            console.log('Rules loaded:', response);
            
            if (response.length === 0) {
                container.innerHTML = '<p>No rules found. Create your first discount rule!</p>';
                return;
            }
            
            let html = `
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>Title</th>
                            <th>Type</th>
                            <th>Value</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
            `;
            
            response.forEach(rule => {
                html += `
                    <tr>
                        <td>${rule.title}</td>
                        <td>${rule.discount_type}</td>
                        <td>${rule.discount_value}${rule.discount_type === 'percentage' ? '%' : ''}</td>
                        <td><span style="padding: 2px 8px; border-radius: 3px; background: ${rule.status === 'active' ? '#46b450' : '#ccc'}; color: white; font-size: 12px;">${rule.status}</span></td>
                        <td>
                            <button class="button button-small edit-btn" data-id="${rule.id}">Edit</button>
                            <button class="button button-small duplicate-btn" data-id="${rule.id}">Duplicate</button>
                            <button class="button button-small delete-btn" data-id="${rule.id}" style="color: #d63638;">Delete</button>
                        </td>
                    </tr>
                `;
            });
            
            html += '</tbody></table>';
            container.innerHTML = html;
            
            // Add event listeners
            container.querySelectorAll('.edit-btn').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    console.log('Edit rule:', e.target.dataset.id);
                });
            });
            
            container.querySelectorAll('.duplicate-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const ruleId = e.target.dataset.id;
                    console.log('Duplicate rule:', ruleId);
                    
                    try {
                        await wp.apiFetch({
                            path: `/dmwoo/v1/rules/${ruleId}/duplicate`,
                            method: 'POST'
                        });
                        loadAndDisplayRules();
                        alert('Rule duplicated successfully!');
                    } catch (error) {
                        console.error('Error duplicating rule:', error);
                        alert('Error duplicating rule');
                    }
                });
            });
            
            container.querySelectorAll('.delete-btn').forEach(btn => {
                btn.addEventListener('click', async (e) => {
                    const ruleId = e.target.dataset.id;
                    
                    if (!confirm('Are you sure you want to delete this rule?')) {
                        return;
                    }
                    
                    try {
                        await wp.apiFetch({
                            path: `/dmwoo/v1/rules/${ruleId}`,
                            method: 'DELETE'
                        });
                        loadAndDisplayRules();
                        alert('Rule deleted successfully!');
                    } catch (error) {
                        console.error('Error deleting rule:', error);
                        alert('Error deleting rule');
                    }
                });
            });
            
        } catch (error) {
            console.error('Error loading rules:', error);
            container.innerHTML = '<p>Error loading rules. Check console for details.</p>';
        }
    }
    
    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createSimpleInterface);
    } else {
        createSimpleInterface();
    }
    
    setTimeout(createSimpleInterface, 1000);
})();