/**
 * Admin App - WordPress Native React
 */
(function() {
    const { createElement, useState } = wp.element;
    const { Button, TabPanel } = wp.components;
    const { __ } = wp.i18n;

    const AdminApp = () => {
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
                            createElement('h2', null, __('Discount Rules', 'discount-manager-woocommerce')),
                            createElement('p', null, __('Create and manage your discount rules here.', 'discount-manager-woocommerce')),
                            createElement(Button, {
                                variant: 'primary'
                            }, __('Add New Rule', 'discount-manager-woocommerce'))
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