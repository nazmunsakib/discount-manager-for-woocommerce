/**
 * Admin App - WordPress Native React
 */
(function() {
    const { createElement, useState, useEffect } = wp.element;
    const { Button, TabPanel } = wp.components;
    const { __ } = wp.i18n;

    const AdminApp = () => {
        const [rules, setRules] = useState([]);
        const [loading, setLoading] = useState(true);

        // Load rules on component mount
        useEffect(() => {
            loadRules();
        }, []);

        const loadRules = async () => {
            try {
                const response = await wp.apiFetch({
                    path: '/dmwoo/v1/rules'
                });
                setRules(response);
            } catch (error) {
                console.error('Error loading rules:', error);
            } finally {
                setLoading(false);
            }
        };

        const handleEdit = (ruleId) => {
            console.log('Edit rule:', ruleId);
            // TODO: Implement edit functionality
        };

        const handleDelete = async (ruleId) => {
            if (!confirm(__('Are you sure you want to delete this rule?', 'discount-manager-woocommerce'))) {
                return;
            }
            
            try {
                await wp.apiFetch({
                    path: `/dmwoo/v1/rules/${ruleId}`,
                    method: 'DELETE'
                });
                loadRules();
            } catch (error) {
                console.error('Error deleting rule:', error);
                alert(__('Error deleting rule', 'discount-manager-woocommerce'));
            }
        };

        const handleDuplicate = async (ruleId) => {
            try {
                await wp.apiFetch({
                    path: `/dmwoo/v1/rules/${ruleId}/duplicate`,
                    method: 'POST'
                });
                loadRules();
            } catch (error) {
                console.error('Error duplicating rule:', error);
                alert(__('Error duplicating rule', 'discount-manager-woocommerce'));
            }
        };

        const tabs = [
            {
                name: 'rules',
                title: __('Discount Rules', 'discount-manager-woocommerce'),
                className: 'discount-rules-tab'
            },
            {
                name: 'settings', 
                title: __('Settings', 'discount-manager-woocommerce'),
                className: 'settings-tab'
            }
        ];

        return createElement('div', { className: 'discount-manager-admin' },
            createElement(TabPanel, {
                className: 'discount-manager-tabs',
                activeClass: 'active-tab',
                tabs: tabs,
                children: (tab) => {
                    if (tab.name === 'rules') {
                        return createElement('div', { className: 'discount-rules-tab' },
                            createElement('div', { className: 'rules-header' },
                                createElement('h2', null, __('Discount Rules', 'discount-manager-woocommerce')),
                                createElement(Button, {
                                    variant: 'primary',
                                    className: 'add-rule-btn'
                                }, __('Add New Rule', 'discount-manager-woocommerce'))
                            ),
                            loading ? 
                                createElement('p', null, __('Loading rules...', 'discount-manager-woocommerce')) :
                                createElement('div', { className: 'rules-list' },
                                    rules.length === 0 ? 
                                        createElement('p', null, __('No rules found. Create your first discount rule!', 'discount-manager-woocommerce')) :
                                        createElement('table', { className: 'wp-list-table widefat fixed striped' },
                                            createElement('thead', null,
                                                createElement('tr', null,
                                                    createElement('th', null, __('Title', 'discount-manager-woocommerce')),
                                                    createElement('th', null, __('Type', 'discount-manager-woocommerce')),
                                                    createElement('th', null, __('Value', 'discount-manager-woocommerce')),
                                                    createElement('th', null, __('Status', 'discount-manager-woocommerce')),
                                                    createElement('th', null, __('Actions', 'discount-manager-woocommerce'))
                                                )
                                            ),
                                            createElement('tbody', null,
                                                rules.map(rule => 
                                                    createElement('tr', { key: rule.id },
                                                        createElement('td', null, rule.title),
                                                        createElement('td', null, rule.discount_type),
                                                        createElement('td', null, rule.discount_value + (rule.discount_type === 'percentage' ? '%' : '')),
                                                        createElement('td', null, 
                                                            createElement('span', { 
                                                                className: `status-${rule.status}`,
                                                                style: { 
                                                                    padding: '2px 8px',
                                                                    borderRadius: '3px',
                                                                    backgroundColor: rule.status === 'active' ? '#46b450' : '#ccc',
                                                                    color: 'white',
                                                                    fontSize: '12px'
                                                                }
                                                            }, rule.status)
                                                        ),
                                                        createElement('td', null,
                                                            createElement('div', { className: 'row-actions' },
                                                                createElement(Button, {
                                                                    variant: 'secondary',
                                                                    size: 'small',
                                                                    onClick: () => handleEdit(rule.id)
                                                                }, __('Edit', 'discount-manager-woocommerce')),
                                                                createElement(Button, {
                                                                    variant: 'secondary',
                                                                    size: 'small',
                                                                    onClick: () => handleDuplicate(rule.id)
                                                                }, __('Duplicate', 'discount-manager-woocommerce')),
                                                                createElement(Button, {
                                                                    variant: 'secondary',
                                                                    size: 'small',
                                                                    onClick: () => handleDelete(rule.id),
                                                                    style: { color: '#d63638' }
                                                                }, __('Delete', 'discount-manager-woocommerce'))
                                                            )
                                                        )
                                                    )
                                                )
                                            )
                                        )
                                )
                        );
                    }
                    
                    if (tab.name === 'settings') {
                        return createElement('div', { className: 'settings-tab' },
                            createElement('h2', null, __('Settings', 'discount-manager-woocommerce')),
                            createElement('form', null,
                                createElement('table', { className: 'form-table' },
                                    createElement('tbody', null,
                                        createElement('tr', null,
                                            createElement('th', { scope: 'row' }, __('Enable Discount Manager', 'discount-manager-woocommerce')),
                                            createElement('td', null,
                                                createElement('label', null,
                                                    createElement('input', { type: 'checkbox', defaultChecked: true }),
                                                    ' ' + __('Enable discount functionality', 'discount-manager-woocommerce')
                                                )
                                            )
                                        )
                                    )
                                ),
                                createElement('p', { className: 'submit' },
                                    createElement(Button, {
                                        variant: 'primary',
                                        type: 'submit'
                                    }, __('Save Changes', 'discount-manager-woocommerce'))
                                )
                            )
                        );
                    }
                }
            })
        );
    };

    // Initialize when DOM is ready
    function initializeApp() {
        console.log('DMWOO: Initializing app...');
        const container = document.getElementById('dmwoo-admin-root');
        
        if (!container) {
            console.error('DMWOO: Container not found');
            return;
        }
        
        if (typeof wp === 'undefined' || typeof wp.element === 'undefined') {
            console.error('DMWOO: wp.element not available');
            return;
        }
        
        console.log('DMWOO: Rendering React app...');
        try {
            wp.element.render(createElement(AdminApp), container);
            console.log('DMWOO: React app rendered successfully');
        } catch (error) {
            console.error('DMWOO: Error rendering React app:', error);
            container.innerHTML = '<div class="notice notice-error"><p>Error loading interface: ' + error.message + '</p></div>';
        }
    }
    
    // Try multiple initialization methods
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeApp);
    } else {
        initializeApp();
    }
    
    // Fallback initialization
    setTimeout(initializeApp, 1000);
})();