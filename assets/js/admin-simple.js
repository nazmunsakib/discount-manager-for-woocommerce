/**
 * Simple Admin App - WordPress Native React
 */
(function() {
    console.log('DMWOO: Simple admin script loading...');
    
    const { createElement, useState, useEffect } = wp.element;
    const { Button, Card, CardBody, TextControl, SelectControl } = wp.components;
    const { __ } = wp.i18n;

    // Simple Rules List
    const SimpleRulesList = ({ rules, onAddNew }) => {
        return createElement('div', { className: 'dmwoo-simple-rules' },
            createElement('div', { className: 'dmwoo-header' },
                createElement('h2', null, __('Discount Rules', 'discount-manager-woocommerce')),
                createElement(Button, {
                    variant: 'primary',
                    onClick: onAddNew
                }, __('Add New Rule', 'discount-manager-woocommerce'))
            ),
            rules.length === 0 ? 
                createElement('p', null, __('No rules found. Create your first discount rule!', 'discount-manager-woocommerce')) :
                createElement('div', { className: 'dmwoo-rules-grid' },
                    rules.map(rule => 
                        createElement(Card, { key: rule.id },
                            createElement(CardBody, null,
                                createElement('h3', null, rule.title),
                                createElement('p', null, `${rule.discount_value}% ${rule.discount_type} discount`),
                                createElement('span', { className: `dmwoo-status-${rule.status}` }, rule.status)
                            )
                        )
                    )
                )
        );
    };

    // Simple Add Rule Form
    const SimpleAddForm = ({ onSave, onCancel }) => {
        const [title, setTitle] = useState('');
        const [discountValue, setDiscountValue] = useState(10);

        const handleSave = () => {
            if (!title.trim()) {
                alert(__('Please enter a rule title', 'discount-manager-woocommerce'));
                return;
            }
            
            onSave({
                title: title.trim(),
                discount_type: 'percentage',
                discount_value: discountValue,
                status: 'active'
            });
        };

        return createElement('div', { className: 'dmwoo-add-form' },
            createElement('h2', null, __('Add New Discount Rule', 'discount-manager-woocommerce')),
            
            createElement(TextControl, {
                label: __('Rule Title', 'discount-manager-woocommerce'),
                value: title,
                onChange: setTitle,
                placeholder: __('e.g., Summer Sale 20% Off', 'discount-manager-woocommerce')
            }),
            
            createElement(TextControl, {
                label: __('Discount Percentage', 'discount-manager-woocommerce'),
                type: 'number',
                value: discountValue,
                onChange: (value) => setDiscountValue(parseFloat(value) || 0),
                min: 0,
                max: 100
            }),
            
            createElement('div', { className: 'dmwoo-form-buttons' },
                createElement(Button, {
                    variant: 'primary',
                    onClick: handleSave
                }, __('Save Rule', 'discount-manager-woocommerce')),
                createElement(Button, {
                    variant: 'secondary',
                    onClick: onCancel
                }, __('Cancel', 'discount-manager-woocommerce'))
            )
        );
    };

    // Main Simple App
    const SimpleApp = () => {
        const [rules, setRules] = useState([]);
        const [loading, setLoading] = useState(true);
        const [showAddForm, setShowAddForm] = useState(false);
        const [message, setMessage] = useState('');

        // Load rules
        useEffect(() => {
            loadRules();
        }, []);

        const loadRules = async () => {
            try {
                console.log('DMWOO: Loading rules...');
                const response = await wp.apiFetch({ path: '/dmwoo/v1/rules' });
                console.log('DMWOO: Rules loaded:', response);
                setRules(response || []);
            } catch (error) {
                console.error('DMWOO: Error loading rules:', error);
                setMessage('Error loading rules: ' + error.message);
            } finally {
                setLoading(false);
            }
        };

        const handleSaveRule = async (ruleData) => {
            try {
                console.log('DMWOO: Saving rule:', ruleData);
                await wp.apiFetch({
                    path: '/dmwoo/v1/rules',
                    method: 'POST',
                    data: ruleData
                });
                setMessage('Rule created successfully!');
                setShowAddForm(false);
                loadRules();
            } catch (error) {
                console.error('DMWOO: Error saving rule:', error);
                setMessage('Error saving rule: ' + error.message);
            }
        };

        if (loading) {
            return createElement('div', { className: 'dmwoo-loading' }, 
                __('Loading...', 'discount-manager-woocommerce')
            );
        }

        return createElement('div', { className: 'dmwoo-simple-app' },
            message && createElement('div', { 
                className: message.includes('Error') ? 'notice notice-error' : 'notice notice-success',
                style: { margin: '10px 0' }
            }, createElement('p', null, message)),
            
            showAddForm ? 
                createElement(SimpleAddForm, {
                    onSave: handleSaveRule,
                    onCancel: () => setShowAddForm(false)
                }) :
                createElement(SimpleRulesList, {
                    rules: rules,
                    onAddNew: () => setShowAddForm(true)
                })
        );
    };

    // Initialize
    function initSimpleApp() {
        console.log('DMWOO: Initializing simple app...');
        
        const container = document.getElementById('dmwoo-admin-root');
        if (!container) {
            console.error('DMWOO: Container not found');
            return;
        }

        if (typeof wp === 'undefined' || typeof wp.element === 'undefined') {
            console.error('DMWOO: WordPress React not available');
            container.innerHTML = '<div class="notice notice-error"><p>WordPress React components not loaded. <a href="?page=discount-manager&fallback=1">Use simple interface</a></p></div>';
            return;
        }

        try {
            console.log('DMWOO: Rendering simple app...');
            wp.element.render(createElement(SimpleApp), container);
            console.log('DMWOO: Simple app rendered successfully');
        } catch (error) {
            console.error('DMWOO: Error rendering simple app:', error);
            container.innerHTML = '<div class="notice notice-error"><p>Error: ' + error.message + '</p></div>';
        }
    }

    // Initialize when ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initSimpleApp);
    } else {
        initSimpleApp();
    }
})();