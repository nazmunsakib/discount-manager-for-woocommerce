/**
 * Clean React Admin App
 */
(function() {
    'use strict';
    
    function waitForWP(callback) {
        if (typeof wp !== 'undefined' && wp.element && wp.components && wp.i18n && wp.apiFetch) {
            callback();
        } else {
            setTimeout(function() { waitForWP(callback); }, 100);
        }
    }
    
    function initApp() {
        var createElement = wp.element.createElement;
        var useState = wp.element.useState;
        var useEffect = wp.element.useEffect;
        var useRef = wp.element.useRef;
        var Button = wp.components.Button;
        var Card = wp.components.Card;
        var CardBody = wp.components.CardBody;
        var TextControl = wp.components.TextControl;
        var SelectControl = wp.components.SelectControl;
        var Modal = wp.components.Modal;
        var __ = wp.i18n.__;
        
        // Rules List Component
        function RulesList(props) {
            var rules = props.rules;
            var onEdit = props.onEdit;
            var onDelete = props.onDelete;
            var onAdd = props.onAdd;
            var onToggleStatus = props.onToggleStatus;
            var onDuplicate = props.onDuplicate;
            
            return createElement('div', { className: 'dmwoo-rules-container' },
                createElement('div', { className: 'dmwoo-header' },
                    createElement('h2', null, __('Discount Rules', 'discount-manager-woocommerce')),
                    createElement(Button, {
                        variant: 'primary',
                        onClick: onAdd
                    }, __('Add New Rule', 'discount-manager-woocommerce'))
                ),
                
                rules.length === 0 ? 
                    createElement('div', { className: 'dmwoo-empty' },
                        createElement('p', null, __('No discount rules found.', 'discount-manager-woocommerce')),
                        createElement('p', null, __('Create your first rule to get started!', 'discount-manager-woocommerce'))
                    ) :
                    createElement('div', { className: 'dmwoo-rules-grid' },
                        rules.map(function(rule) {
                            return createElement(Card, { key: rule.id, className: 'dmwoo-rule-card' },
                                createElement(CardBody, null,
                                    createElement('div', { className: 'dmwoo-rule-header' },
                                        createElement('h3', null, rule.title),
                                        createElement('div', { className: 'dmwoo-toggle-container' },
                                            createElement('label', { className: 'dmwoo-toggle-switch' },
                                                createElement('input', {
                                                    type: 'checkbox',
                                                    checked: rule.status === 'active',
                                                    onChange: function() { onToggleStatus(rule); }
                                                }),
                                                createElement('span', { className: 'dmwoo-toggle-slider' }),
                                                createElement('span', { className: 'dmwoo-toggle-label' }, 
                                                    rule.status === 'active' ? __('Active', 'discount-manager-woocommerce') : __('Inactive', 'discount-manager-woocommerce')
                                                )
                                            )
                                        )
                                    ),
                                    createElement('p', null, 
                                        rule.discount_type === 'percentage' ? 
                                            rule.discount_value + '% discount' :
                                        rule.discount_type === 'fixed' ?
                                            rule.discount_value + ' fixed discount' :
                                            'Bulk discount (' + (rule.conditions && rule.conditions.bulk_ranges ? rule.conditions.bulk_ranges.length : 0) + ' ranges)'
                                    ),
                                    rule.description && createElement('p', { className: 'dmwoo-description' }, rule.description),
                                    createElement('div', { className: 'dmwoo-rule-actions' },
                                        createElement(Button, {
                                            variant: 'secondary',
                                            size: 'small',
                                            onClick: function() { onEdit(rule); }
                                        }, __('Edit', 'discount-manager-woocommerce')),
                                        createElement(Button, {
                                            variant: 'secondary',
                                            size: 'small',
                                            onClick: function() { onDuplicate(rule.id); }
                                        }, __('Duplicate', 'discount-manager-woocommerce')),
                                        createElement(Button, {
                                            variant: 'link',
                                            isDestructive: true,
                                            size: 'small',
                                            onClick: function() { onDelete(rule.id); }
                                        }, __('Delete', 'discount-manager-woocommerce'))
                                    )
                                )
                            );
                        })
                    )
            );
        }
        
        // Rule Form Component
        function RuleForm(props) {
            var rule = props.rule;
            var onSave = props.onSave;
            var onCancel = props.onCancel;
            
            var titleState = useState(rule ? rule.title : '');
            var title = titleState[0];
            var setTitle = titleState[1];
            
            var descriptionState = useState(rule ? rule.description : '');
            var description = descriptionState[0];
            var setDescription = descriptionState[1];
            
            var discountTypeState = useState(rule ? rule.discount_type : 'percentage');
            var discountType = discountTypeState[0];
            var setDiscountType = discountTypeState[1];
            
            var discountValueState = useState(rule ? rule.discount_value : 10);
            var discountValue = discountValueState[0];
            var setDiscountValue = discountValueState[1];
            
            var parsedConditions = null;
            if (rule && rule.conditions) {
                parsedConditions = typeof rule.conditions === 'string' ? JSON.parse(rule.conditions) : rule.conditions;
            }
            
            var statusState = useState(rule ? rule.status : 'active');
            var status = statusState[0];
            var setStatus = statusState[1];
            
            var minSubtotalState = useState(parsedConditions ? (parsedConditions.min_subtotal || '') : '');
            var minSubtotal = minSubtotalState[0];
            var setMinSubtotal = minSubtotalState[1];
            
            var minQuantityState = useState(parsedConditions ? (parsedConditions.min_quantity || '') : '');
            var minQuantity = minQuantityState[0];
            var setMinQuantity = minQuantityState[1];
            
            var usageLimitState = useState(rule ? rule.usage_limit : '');
            var usageLimit = usageLimitState[0];
            var setUsageLimit = usageLimitState[1];
            
            var priorityState = useState(rule ? rule.priority : 10);
            var priority = priorityState[0];
            var setPriority = priorityState[1];
            
            var applyToState = useState(rule && rule.filters ? rule.filters.apply_to : 'all_products');
            var productSearchState = useState('');
            var searchResultsState = useState([]);
            var selectedProductsState = useState(rule && rule.filters && rule.filters.selected_products ? rule.filters.selected_products : []);
            var filterMethodState = useState(rule && rule.filters ? rule.filters.filter_method : 'include');
            

            
            function searchProducts(query) {
                if (query.length < 3) {
                    searchResultsState[1]([]);
                    return;
                }
                
                wp.apiFetch({
                    path: '/dmwoo/v1/products?search=' + encodeURIComponent(query)
                })
                .then(function(products) {
                    searchResultsState[1](products);
                })
                .catch(function(error) {
                    searchResultsState[1]([]);
                });
            }
            
            function addSelectedProduct(product) {
                var currentSelected = selectedProductsState[0];
                var isAlreadySelected = currentSelected.some(function(p) { return p.id === product.id; });
                
                if (!isAlreadySelected) {
                    selectedProductsState[1](currentSelected.concat([product]));
                }
                
                productSearchState[1]('');
                searchResultsState[1]([]);
            }
            
            function removeSelectedProduct(productId) {
                var currentSelected = selectedProductsState[0];
                var filtered = currentSelected.filter(function(p) { return p.id !== productId; });
                selectedProductsState[1](filtered);
            }
            

            

            
            function handleSave() {
                if (!title || !title.trim()) {
                    alert(__('Please enter a rule title', 'discount-manager-woocommerce'));
                    return;
                }
                
                var applyTo = applyToState[0];
                var filterMethod = filterMethodState[0];
                var selectedProducts = selectedProductsState[0];
                
                var data = {
                    title: title.trim(),
                    description: description ? description.trim() : '',
                    discount_type: discountType,
                    discount_value: discountValue,
                    filters: {
                        apply_to: applyTo,
                        filter_method: applyTo === 'specific_products' ? filterMethod : 'include',
                        selected_products: applyTo === 'specific_products' ? selectedProducts : []
                    },
                    conditions: {
                        min_subtotal: minSubtotal ? parseFloat(minSubtotal) : null,
                        min_quantity: minQuantity ? parseInt(minQuantity) : null
                    },
                    usage_limit: usageLimit ? parseInt(usageLimit) : null,
                    priority: priority,
                    status: status
                };
                
                onSave(data);
            }
            

            
            return createElement('div', { className: 'dmwoo-form-container' },
                createElement('h2', null, rule ? __('Edit Rule', 'discount-manager-woocommerce') : __('Add New Rule', 'discount-manager-woocommerce')),
                
                // Basic Info
                createElement('div', { className: 'dmwoo-form-section' },
                    createElement('h3', null, __('Basic Information', 'discount-manager-woocommerce')),
                    createElement('div', { className: 'dmwoo-form-field' },
                        createElement('label', { className: 'dmwoo-form-label' }, __('Rule Title', 'discount-manager-woocommerce')),
                        createElement('input', {
                            type: 'text',
                            value: title,
                            onChange: function(e) { setTitle(e.target.value); },
                            placeholder: __('e.g., Summer Sale 20% Off', 'discount-manager-woocommerce')
                        })
                    ),
                    createElement('div', { className: 'dmwoo-form-field' },
                        createElement('label', { className: 'dmwoo-form-label' }, __('Description (Optional)', 'discount-manager-woocommerce')),
                        createElement('input', {
                            type: 'text',
                            value: description,
                            onChange: function(e) { setDescription(e.target.value); },
                            placeholder: __('Brief description of this discount rule', 'discount-manager-woocommerce')
                        })
                    ),
                    createElement('div', { className: 'dmwoo-form-toggle' },
                        createElement('label', { className: 'dmwoo-form-label' }, __('Status', 'discount-manager-woocommerce')),
                        createElement('div', { className: 'dmwoo-toggle-container' },
                            createElement('label', { className: 'dmwoo-toggle-switch' },
                                createElement('input', {
                                    type: 'checkbox',
                                    checked: status === 'active',
                                    onChange: function(e) { setStatus(e.target.checked ? 'active' : 'inactive'); }
                                }),
                                createElement('span', { className: 'dmwoo-toggle-slider' }),
                                createElement('span', { className: 'dmwoo-toggle-label' }, 
                                    status === 'active' ? __('Active', 'discount-manager-woocommerce') : __('Inactive', 'discount-manager-woocommerce')
                                )
                            )
                        )
                    )
                ),
                
                // Discount Config
                createElement('div', { className: 'dmwoo-form-section' },
                    createElement('h3', null, __('Discount Configuration', 'discount-manager-woocommerce')),
                    createElement('div', { className: 'dmwoo-form-field' },
                        createElement('label', { className: 'dmwoo-form-label' }, __('Discount Type', 'discount-manager-woocommerce')),
                        createElement('select', {
                            value: discountType,
                            onChange: function(e) { setDiscountType(e.target.value); }
                        },
                            createElement('option', { value: 'percentage' }, __('Percentage Discount', 'discount-manager-woocommerce')),
                            createElement('option', { value: 'fixed' }, __('Fixed Discount', 'discount-manager-woocommerce'))
                        )
                    ),
                    
                    discountType === 'percentage' && createElement('div', { className: 'dmwoo-form-field' },
                        createElement('label', { className: 'dmwoo-form-label' }, __('Discount Percentage', 'discount-manager-woocommerce')),
                        createElement('input', {
                            type: 'number',
                            value: discountValue,
                            onChange: function(e) { setDiscountValue(parseFloat(e.target.value) || 0); },
                            min: 0,
                            max: 100,
                            step: 0.01
                        })
                    ),
                    
                    discountType === 'fixed' && createElement('div', { className: 'dmwoo-form-field' },
                        createElement('label', { className: 'dmwoo-form-label' }, __('Fixed Discount Amount', 'discount-manager-woocommerce')),
                        createElement('input', {
                            type: 'number',
                            value: discountValue,
                            onChange: function(e) { setDiscountValue(parseFloat(e.target.value) || 0); },
                            min: 0,
                            step: 0.01
                        })
                    ),
                    

                ),
                
                // Filter Section
                createElement('div', { className: 'dmwoo-form-section' },
                    createElement('h3', null, __('Product Selection', 'discount-manager-woocommerce')),
                    createElement('p', { style: { marginBottom: '15px', color: '#646970' } }, 
                        __('Choose which products get the discount', 'discount-manager-woocommerce')
                    ),
                    createElement('div', { className: 'dmwoo-conditions-grid' },
                        createElement('div', { className: 'dmwoo-form-field' },
                            createElement('label', { className: 'dmwoo-form-label' }, __('Apply to', 'discount-manager-woocommerce')),
                            createElement('select', {
                                value: applyToState[0],
                                onChange: function(e) { applyToState[1](e.target.value); }
                            },
                                createElement('option', { value: 'all_products' }, __('All products', 'discount-manager-woocommerce')),
                                createElement('option', { value: 'specific_products' }, __('Specific products', 'discount-manager-woocommerce'))
                            )
                        ),
                        applyToState[0] === 'specific_products' && createElement('div', { className: 'dmwoo-form-field' },
                            createElement('label', { className: 'dmwoo-form-label' }, __('Filter method', 'discount-manager-woocommerce')),
                            createElement('select', {
                                value: filterMethodState[0],
                                onChange: function(e) { filterMethodState[1](e.target.value); }
                            },
                                createElement('option', { value: 'include' }, __('Include selected products', 'discount-manager-woocommerce')),
                                createElement('option', { value: 'exclude' }, __('Exclude selected products', 'discount-manager-woocommerce'))
                            ),
                            createElement('p', { 
                                className: 'dmwoo-filter-instruction' + (filterMethodState[0] === 'exclude' ? ' exclude' : '')
                            }, 
                                filterMethodState[0] === 'include' ? 
                                    __('✓ Discount will apply ONLY to the selected products', 'discount-manager-woocommerce') :
                                    __('✗ Discount will apply to ALL products EXCEPT the selected ones', 'discount-manager-woocommerce')
                            )
                        )
                    ),
                    applyToState[0] === 'specific_products' && createElement('div', { className: 'dmwoo-product-selector' },
                        createElement('div', { className: 'dmwoo-form-field' },
                            createElement('label', { className: 'dmwoo-form-label' }, __('Search and select products', 'discount-manager-woocommerce')),
                            createElement('input', {
                                type: 'text',
                                placeholder: __('Type to search products...', 'discount-manager-woocommerce'),
                                value: productSearchState[0],
                                onChange: function(e) { 
                                    productSearchState[1](e.target.value);
                                    if (e.target.value.length > 2) {
                                        searchProducts(e.target.value);
                                    }
                                },
                                className: 'dmwoo-product-search'
                            })
                        ),
                        searchResultsState[0].length > 0 && createElement('div', { className: 'dmwoo-search-results' },
                            searchResultsState[0].map(function(product) {
                                return createElement('div', {
                                    key: product.id,
                                    className: 'dmwoo-search-result',
                                    onClick: function() { addSelectedProduct(product); }
                                },
                                    createElement('span', { className: 'product-name' }, product.name),
                                    createElement('span', { className: 'product-price' }, product.price)
                                );
                            })
                        ),
                        selectedProductsState[0].length > 0 && createElement('div', { className: 'dmwoo-selected-products' },
                            createElement('h4', null, __('Selected Products', 'discount-manager-woocommerce')),
                            selectedProductsState[0].map(function(product) {
                                return createElement('div', {
                                    key: product.id,
                                    className: 'dmwoo-selected-product'
                                },
                                    createElement('span', { className: 'product-name' }, product.name),
                                    createElement('button', {
                                        type: 'button',
                                        className: 'dmwoo-remove-product',
                                        onClick: function() { removeSelectedProduct(product.id); }
                                    }, '×')
                                );
                            })
                        )
                    )
                ),
                
                // Conditions
                createElement('div', { className: 'dmwoo-form-section' },
                    createElement('h3', null, __('Conditions (Optional)', 'discount-manager-woocommerce')),
                    createElement('div', { className: 'dmwoo-conditions-grid' },
                        createElement('div', { className: 'dmwoo-form-field' },
                            createElement('label', { className: 'dmwoo-form-label' }, __('Minimum Cart Subtotal', 'discount-manager-woocommerce')),
                            createElement('input', {
                                type: 'number',
                                value: minSubtotal,
                                onChange: function(e) { setMinSubtotal(e.target.value); },
                                placeholder: __('No minimum', 'discount-manager-woocommerce')
                            })
                        ),
                        createElement('div', { className: 'dmwoo-form-field' },
                            createElement('label', { className: 'dmwoo-form-label' }, __('Minimum Quantity', 'discount-manager-woocommerce')),
                            createElement('input', {
                                type: 'number',
                                value: minQuantity,
                                onChange: function(e) { setMinQuantity(e.target.value); },
                                placeholder: __('No minimum', 'discount-manager-woocommerce')
                            })
                        )
                    )
                ),
                
                // Advanced
                createElement('div', { className: 'dmwoo-form-section' },
                    createElement('h3', null, __('Advanced Settings', 'discount-manager-woocommerce')),
                    createElement('div', { className: 'dmwoo-advanced-grid' },
                        createElement('div', { className: 'dmwoo-form-field' },
                            createElement('label', { className: 'dmwoo-form-label' }, __('Usage Limit', 'discount-manager-woocommerce')),
                            createElement('input', {
                                type: 'number',
                                value: usageLimit,
                                onChange: function(e) { setUsageLimit(e.target.value); },
                                placeholder: __('Unlimited', 'discount-manager-woocommerce'),
                                min: 0
                            }),
                            createElement('p', { className: 'dmwoo-field-help' }, 
                                __('Maximum number of times this discount can be used. Leave empty for unlimited usage.', 'discount-manager-woocommerce')
                            )
                        ),
                        createElement('div', { className: 'dmwoo-form-field' },
                            createElement('label', { className: 'dmwoo-form-label' }, __('Priority', 'discount-manager-woocommerce')),
                            createElement('input', {
                                type: 'number',
                                value: priority,
                                onChange: function(e) { setPriority(parseInt(e.target.value) || 10); },
                                min: 1,
                                max: 999
                            }),
                            createElement('p', { className: 'dmwoo-field-help' }, 
                                __('Lower numbers = higher priority. When multiple rules match, lower priority number applies first. Default: 10', 'discount-manager-woocommerce')
                            )
                        )
                    )
                ),
                
                // Actions
                createElement('div', { className: 'dmwoo-form-actions' },
                    createElement(Button, {
                        variant: 'primary',
                        onClick: handleSave
                    }, rule ? __('Update Rule', 'discount-manager-woocommerce') : __('Create Rule', 'discount-manager-woocommerce')),
                    createElement(Button, {
                        variant: 'secondary',
                        onClick: onCancel
                    }, __('Cancel', 'discount-manager-woocommerce'))
                )
            );
        }
        
        // Settings Component
        function Settings() {
            var enabledState = useState(true);
            var enabled = enabledState[0];
            var setEnabled = enabledState[1];
            
            var calculateFromState = useState('regular_price');
            var calculateFrom = calculateFromState[0];
            var setCalculateFrom = calculateFromState[1];
            
            var applyRulesState = useState('biggest_discount');
            var applyRules = applyRulesState[0];
            var setApplyRules = applyRulesState[1];
            
            var couponBehaviorState = useState('run_both');
            var couponBehavior = couponBehaviorState[0];
            var setCouponBehavior = couponBehaviorState[1];
            
            var showBulkTableState = useState(true);
            var showBulkTable = showBulkTableState[0];
            var setShowBulkTable = showBulkTableState[1];
            
            var showSaleBadgeState = useState('disabled');
            var showSaleBadge = showSaleBadgeState[0];
            var setShowSaleBadge = showSaleBadgeState[1];
            
            var showStrikeoutState = useState(true);
            var showStrikeout = showStrikeoutState[0];
            var setShowStrikeout = showStrikeoutState[1];
            

            
            var noticeState = useState(null);
            var notice = noticeState[0];
            var setNotice = noticeState[1];
            
            useEffect(function() {
                loadSettings();
            }, []);
            
            useEffect(function() {
                if (notice) {
                    var timer = setTimeout(function() {
                        setNotice(null);
                    }, 3000);
                    return function() { clearTimeout(timer); };
                }
            }, [notice]);
            
            function loadSettings() {
                wp.apiFetch({ path: '/dmwoo/v1/settings' })
                    .then(function(settings) {
                        if (settings.calculate_from) setCalculateFrom(settings.calculate_from);
                        if (settings.apply_product_discount_to) setApplyRules(settings.apply_product_discount_to);
                        if (settings.show_sale_badge) setShowSaleBadge(settings.show_sale_badge);
                        if (settings.show_bulk_table !== undefined) setShowBulkTable(settings.show_bulk_table);
                        if (settings.show_strikeout !== undefined) setShowStrikeout(settings.show_strikeout);

                    })
                    .catch(function(error) {});
            }
            
            function handleSave() {
                var settings = {
                    calculate_from: calculateFrom,
                    apply_product_discount_to: applyRules,
                    show_sale_badge: showSaleBadge,
                    show_bulk_table: showBulkTable,
                    show_strikeout: showStrikeout,

                };
                
                wp.apiFetch({
                    path: '/dmwoo/v1/settings',
                    method: 'POST',
                    data: settings
                })
                .then(function() {
                    setNotice({ type: 'success', message: __('Settings saved successfully!', 'discount-manager-woocommerce') });
                })
                .catch(function(error) {
                    setNotice({ type: 'error', message: __('Failed to save settings', 'discount-manager-woocommerce') });
                });
            }
            
            return createElement('div', { className: 'dmwoo-settings-container' },
                notice && createElement('div', { className: 'dmwoo-toast dmwoo-toast-' + notice.type },
                    createElement('span', { className: 'dmwoo-toast-message' }, notice.message),
                    createElement('button', { 
                        className: 'dmwoo-toast-close',
                        onClick: function() { setNotice(null); }
                    }, '×')
                ),
                
                createElement('div', { className: 'dmwoo-settings-grid' },
                    // General Settings
                    createElement('div', { className: 'dmwoo-form-section' },
                    createElement('h3', null, 
                        createElement('span', { className: 'dashicons dashicons-admin-settings' }),
                        ' ' + __('General Settings', 'discount-manager-woocommerce')
                    ),
                    createElement('div', { className: 'dmwoo-form-toggle' },
                        createElement('label', { className: 'dmwoo-form-label' }, __('Enable Discount Manager', 'discount-manager-woocommerce')),
                        createElement('div', { className: 'dmwoo-toggle-container' },
                            createElement('label', { className: 'dmwoo-toggle-switch' },
                                createElement('input', {
                                    type: 'checkbox',
                                    checked: enabled,
                                    onChange: function(e) { setEnabled(e.target.checked); }
                                }),
                                createElement('span', { className: 'dmwoo-toggle-slider' }),
                                createElement('span', { className: 'dmwoo-toggle-label' }, 
                                    enabled ? __('Enabled', 'discount-manager-woocommerce') : __('Disabled', 'discount-manager-woocommerce')
                                )
                            )
                        )
                    ),
                    createElement('div', { className: 'dmwoo-select-wrapper' },
                        createElement('label', { className: 'dmwoo-form-label' }, __('Calculate discount from', 'discount-manager-woocommerce')),
                        createElement('select', {
                            value: calculateFrom,
                            onChange: function(e) { setCalculateFrom(e.target.value); }
                        },
                            createElement('option', { value: 'regular_price' }, __('Regular price', 'discount-manager-woocommerce')),
                            createElement('option', { value: 'sale_price' }, __('Sale price', 'discount-manager-woocommerce'))
                        )
                    ),
                    createElement('div', { className: 'dmwoo-select-wrapper' },
                        createElement('label', { className: 'dmwoo-form-label' }, __('Apply discount rules', 'discount-manager-woocommerce')),
                        createElement('select', {
                            value: applyRules,
                            onChange: function(e) { setApplyRules(e.target.value); }
                        },
                            createElement('option', { value: 'first' }, __('First matched rule (respects priority)', 'discount-manager-woocommerce')),
                            createElement('option', { value: 'biggest_discount' }, __('Biggest discount from matched rules', 'discount-manager-woocommerce')),
                            createElement('option', { value: 'lowest_discount' }, __('Lowest discount from matched rules', 'discount-manager-woocommerce')),
                            createElement('option', { value: 'all' }, __('All matched rules', 'discount-manager-woocommerce'))
                        ),
                        createElement('p', { className: 'dmwoo-field-help' }, 
                            __('Choose "First matched rule" to respect priority settings. Rules are checked in priority order (lower number first).', 'discount-manager-woocommerce')
                        )
                    ),
                    createElement('div', { className: 'dmwoo-select-wrapper' },
                        createElement('label', { className: 'dmwoo-form-label' }, __('Coupon behavior', 'discount-manager-woocommerce')),
                        createElement('select', {
                            value: couponBehavior,
                            onChange: function(e) { setCouponBehavior(e.target.value); }
                        },
                            createElement('option', { value: 'run_both' }, __('Let both coupons and discount rules work together', 'discount-manager-woocommerce')),
                            createElement('option', { value: 'disable_coupon' }, __('Disable coupons when discount rules apply', 'discount-manager-woocommerce')),
                            createElement('option', { value: 'disable_rules' }, __('Disable discount rules when coupons apply', 'discount-manager-woocommerce'))
                        )
                    )
                    ),
                    
                    // Product Settings
                    createElement('div', { className: 'dmwoo-form-section' },
                    createElement('h3', null, 
                        createElement('span', { className: 'dashicons dashicons-products' }),
                        ' ' + __('Product Settings', 'discount-manager-woocommerce')
                    ),
                    createElement('div', { className: 'dmwoo-select-wrapper' },
                        createElement('label', { className: 'dmwoo-form-label' }, __('On-sale badge', 'discount-manager-woocommerce')),
                        createElement('select', {
                            value: showSaleBadge,
                            onChange: function(e) { setShowSaleBadge(e.target.value); }
                        },
                            createElement('option', { value: 'when_condition_matches' }, __('Show when rule condition matches', 'discount-manager-woocommerce')),
                            createElement('option', { value: 'at_least_has_any_rules' }, __('Show on products covered by any rule', 'discount-manager-woocommerce')),
                            createElement('option', { value: 'disabled' }, __('Do not show', 'discount-manager-woocommerce'))
                        ),
                        createElement('p', { className: 'dmwoo-field-help' }, 
                            __('Display "Sale!" badge on products with active discount rules.', 'discount-manager-woocommerce')
                        )
                    ),
                    createElement('div', { className: 'dmwoo-form-toggle' },
                        createElement('label', { className: 'dmwoo-form-label' }, __('Show bulk discount table', 'discount-manager-woocommerce')),
                        createElement('div', { className: 'dmwoo-toggle-container' },
                            createElement('label', { className: 'dmwoo-toggle-switch' },
                                createElement('input', {
                                    type: 'checkbox',
                                    checked: showBulkTable,
                                    onChange: function(e) { setShowBulkTable(e.target.checked); }
                                }),
                                createElement('span', { className: 'dmwoo-toggle-slider' }),
                                createElement('span', { className: 'dmwoo-toggle-label' }, 
                                    showBulkTable ? __('Show', 'discount-manager-woocommerce') : __('Hide', 'discount-manager-woocommerce')
                                )
                            )
                        )
                    ),
                    createElement('div', { className: 'dmwoo-form-toggle' },
                        createElement('label', { className: 'dmwoo-form-label' }, __('Show strikeout price', 'discount-manager-woocommerce')),
                        createElement('div', { className: 'dmwoo-toggle-container' },
                            createElement('label', { className: 'dmwoo-toggle-switch' },
                                createElement('input', {
                                    type: 'checkbox',
                                    checked: showStrikeout,
                                    onChange: function(e) { setShowStrikeout(e.target.checked); }
                                }),
                                createElement('span', { className: 'dmwoo-toggle-slider' }),
                                createElement('span', { className: 'dmwoo-toggle-label' }, 
                                    showStrikeout ? __('Show', 'discount-manager-woocommerce') : __('Hide', 'discount-manager-woocommerce')
                                )
                            )
                        )
                    )
                    ),
                    
                    // Promotion Settings
                    createElement('div', { className: 'dmwoo-form-section' },
                    createElement('h3', null, 
                        createElement('span', { className: 'dashicons dashicons-megaphone' }),
                        ' ' + __('Promotion Settings', 'discount-manager-woocommerce')
                    ),

                    )
                ),
                
                createElement('div', { className: 'dmwoo-form-actions' },
                    createElement(Button, {
                        variant: 'primary',
                        onClick: handleSave
                    }, __('Save Settings', 'discount-manager-woocommerce'))
                )
            );
        }
        
        // Main App Component
        function App() {
            var rulesState = useState([]);
            var rules = rulesState[0];
            var setRules = rulesState[1];
            
            var loadingState = useState(true);
            var loading = loadingState[0];
            var setLoading = loadingState[1];
            
            // Get initial state from URL
            var urlParams = new URLSearchParams(window.location.search);
            var initialTab = urlParams.get('tab') || 'rules';
            var initialEditId = urlParams.get('edit');
            
            var editingRuleState = useState(null);
            var editingRule = editingRuleState[0];
            var setEditingRule = editingRuleState[1];
            
            var showFormState = useState(false);
            var showForm = showFormState[0];
            var setShowForm = showFormState[1];
            
            var activeTabState = useState(initialTab);
            var activeTab = activeTabState[0];
            var setActiveTab = activeTabState[1];
            
            var noticeState = useState(null);
            var notice = noticeState[0];
            var setNotice = noticeState[1];
            
            var deleteModalState = useState(null);
            var deleteModal = deleteModalState[0];
            var setDeleteModal = deleteModalState[1];
            
            var initialLoadDone = useRef(false);
            
            useEffect(function() {
                loadRules();
            }, []);
            
            useEffect(function() {
                if (notice) {
                    var timer = setTimeout(function() {
                        setNotice(null);
                    }, 3000);
                    return function() { clearTimeout(timer); };
                }
            }, [notice]);
            
            // Handle initial edit state from URL
            useEffect(function() {
                if (initialLoadDone.current || rules.length === 0) return;
                
                if (initialEditId === 'new') {
                    setShowForm(true);
                    initialLoadDone.current = true;
                } else if (initialEditId) {
                    var ruleToEdit = rules.find(function(r) { 
                        return String(r.id) === String(initialEditId); 
                    });
                    if (ruleToEdit) {
                        setEditingRule(ruleToEdit);
                        setShowForm(true);
                        initialLoadDone.current = true;
                    } else {
                        initialLoadDone.current = true;
                    }
                }
            }, [rules]);
            
            function updateURL(tab, editId) {
                var url = new URL(window.location);
                url.searchParams.set('tab', tab);
                if (editId) {
                    url.searchParams.set('edit', editId);
                } else {
                    url.searchParams.delete('edit');
                }
                window.history.pushState({}, '', url);
            }
            
            function loadRules() {
                wp.apiFetch({ path: '/dmwoo/v1/rules' })
                    .then(function(response) {
                        setRules(response || []);
                        setLoading(false);
                    })
                    .catch(function(error) {
                        setNotice({ type: 'error', message: __('Failed to load rules', 'discount-manager-woocommerce') });
                        setLoading(false);
                    });
            }
            
            function handleSaveRule(ruleData) {
                var request = editingRule ? 
                    wp.apiFetch({
                        path: '/dmwoo/v1/rules/' + editingRule.id,
                        method: 'PUT',
                        data: ruleData
                    }) :
                    wp.apiFetch({
                        path: '/dmwoo/v1/rules',
                        method: 'POST',
                        data: ruleData
                    });
                
                request
                    .then(function() {
                        setNotice({ 
                            type: 'success', 
                            message: editingRule ? 
                                __('Rule updated successfully!', 'discount-manager-woocommerce') :
                                __('Rule created successfully!', 'discount-manager-woocommerce')
                        });
                        loadRules();
                        setShowForm(false);
                        setEditingRule(null);
                        updateURL('rules', null);
                    })
                    .catch(function(error) {
                        setNotice({ type: 'error', message: __('Failed to save rule', 'discount-manager-woocommerce') });
                    });
            }
            
            function handleDeleteRule(ruleId) {
                setDeleteModal(ruleId);
            }
            
            function confirmDelete() {
                var ruleId = deleteModal;
                setDeleteModal(null);
                
                wp.apiFetch({
                    path: '/dmwoo/v1/rules/' + ruleId,
                    method: 'DELETE'
                })
                .then(function() {
                    setNotice({ type: 'success', message: __('Rule deleted successfully!', 'discount-manager-woocommerce') });
                    loadRules();
                })
                .catch(function(error) {
                    setNotice({ type: 'error', message: __('Failed to delete rule', 'discount-manager-woocommerce') });
                });
            }
            
            function handleToggleStatus(rule) {
                var newStatus = rule.status === 'active' ? 'inactive' : 'active';
                var updatedRule = Object.assign({}, rule, { status: newStatus });
                
                wp.apiFetch({
                    path: '/dmwoo/v1/rules/' + rule.id,
                    method: 'PUT',
                    data: updatedRule
                })
                .then(function() {
                    loadRules();
                })
                .catch(function(error) {
                    setNotice({ type: 'error', message: __('Failed to update rule status', 'discount-manager-woocommerce') });
                });
            }
            
            function handleDuplicateRule(ruleId) {
                wp.apiFetch({
                    path: '/dmwoo/v1/rules/' + ruleId + '/duplicate',
                    method: 'POST'
                })
                .then(function() {
                    setNotice({ type: 'success', message: __('Rule duplicated successfully!', 'discount-manager-woocommerce') });
                    loadRules();
                })
                .catch(function(error) {
                    setNotice({ type: 'error', message: __('Failed to duplicate rule', 'discount-manager-woocommerce') });
                });
            }
            
            if (loading) {
                return createElement('div', { className: 'dmwoo-loading' }, 
                    __('Loading discount rules...', 'discount-manager-woocommerce')
                );
            }
            
            return createElement('div', { className: 'dmwoo-app' },
                notice && createElement('div', { className: 'dmwoo-toast dmwoo-toast-' + notice.type },
                    createElement('span', { className: 'dmwoo-toast-message' }, notice.message),
                    createElement('button', { 
                        className: 'dmwoo-toast-close',
                        onClick: function() { setNotice(null); }
                    }, '×')
                ),
                deleteModal && createElement(Modal, {
                    title: __('Delete Rule', 'discount-manager-woocommerce'),
                    onRequestClose: function() { setDeleteModal(null); },
                    className: 'dmwoo-delete-modal'
                },
                    createElement('p', null, __('Are you sure you want to delete this rule? This action cannot be undone.', 'discount-manager-woocommerce')),
                    createElement('div', { className: 'dmwoo-modal-actions' },
                        createElement(Button, {
                            variant: 'secondary',
                            onClick: function() { setDeleteModal(null); }
                        }, __('Cancel', 'discount-manager-woocommerce')),
                        createElement(Button, {
                            variant: 'primary',
                            isDestructive: true,
                            onClick: confirmDelete
                        }, __('Delete', 'discount-manager-woocommerce'))
                    )
                ),
                // Tab Navigation
                createElement('div', { className: 'dmwoo-tabs' },
                    createElement('div', { className: 'dmwoo-tab-nav' },
                        createElement('button', {
                            className: 'dmwoo-tab-button' + (activeTab === 'rules' ? ' active' : ''),
                            onClick: function() { 
                                setActiveTab('rules');
                                updateURL('rules', null);
                            }
                        }, __('Discount Rules', 'discount-manager-woocommerce')),
                        createElement('button', {
                            className: 'dmwoo-tab-button' + (activeTab === 'settings' ? ' active' : ''),
                            onClick: function() { 
                                setActiveTab('settings');
                                updateURL('settings', null);
                            }
                        }, __('Settings', 'discount-manager-woocommerce'))
                    ),
                    
                    createElement('div', { className: 'dmwoo-tab-content' },
                        activeTab === 'rules' && (
                            showForm ? 
                                (editingRule || initialEditId === 'new' ? 
                                    createElement(RuleForm, {
                                        rule: editingRule,
                                        onSave: handleSaveRule,
                                        onCancel: function() {
                                            setShowForm(false);
                                            setEditingRule(null);
                                            updateURL('rules', null);
                                        }
                                    }) :
                                    createElement('div', { className: 'dmwoo-loading' }, 
                                        __('Loading rule data...', 'discount-manager-woocommerce')
                                    )
                                ) :
                                createElement(RulesList, {
                                    rules: rules,
                                    onEdit: function(rule) {
                                        setEditingRule(rule);
                                        setShowForm(true);
                                        updateURL('rules', rule.id);
                                    },
                                    onDelete: handleDeleteRule,
                                    onAdd: function() {
                                        setEditingRule(null);
                                        setShowForm(true);
                                        updateURL('rules', 'new');
                                    },
                                    onToggleStatus: handleToggleStatus,
                                    onDuplicate: handleDuplicateRule
                                })
                        ),
                        
                        activeTab === 'settings' && createElement(Settings)
                    )
                )
            );
        }
        
        // Render the app
        var container = document.getElementById('dmwoo-admin-root');
        if (container) {
            wp.element.render(createElement(App), container);
        }
    }
    
    // Initialize
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            waitForWP(initApp);
        });
    } else {
        waitForWP(initApp);
    }
})();