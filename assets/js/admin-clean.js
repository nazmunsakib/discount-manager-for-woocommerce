/**
 * Clean React Admin App
 */
(function() {
    'use strict';
    
    console.log('DMWOO: Loading admin script...');
    
    function waitForWP(callback) {
        if (typeof wp !== 'undefined' && wp.element && wp.components && wp.i18n && wp.apiFetch) {
            console.log('DMWOO: WordPress ready');
            callback();
        } else {
            setTimeout(function() { waitForWP(callback); }, 100);
        }
    }
    
    function initApp() {
        var createElement = wp.element.createElement;
        var useState = wp.element.useState;
        var useEffect = wp.element.useEffect;
        var Button = wp.components.Button;
        var Card = wp.components.Card;
        var CardBody = wp.components.CardBody;
        var TextControl = wp.components.TextControl;
        var SelectControl = wp.components.SelectControl;
        var Notice = wp.components.Notice;
        var __ = wp.i18n.__;
        
        // Rules List Component
        function RulesList(props) {
            var rules = props.rules;
            var onEdit = props.onEdit;
            var onDelete = props.onDelete;
            var onAdd = props.onAdd;
            var onToggleStatus = props.onToggleStatus;
            
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
            
            var statusState = useState(rule ? rule.status : 'active');
            var status = statusState[0];
            var setStatus = statusState[1];
            
            var minSubtotalState = useState(rule && rule.conditions ? rule.conditions.min_subtotal : '');
            var minSubtotal = minSubtotalState[0];
            var setMinSubtotal = minSubtotalState[1];
            
            var minQuantityState = useState(rule && rule.conditions ? rule.conditions.min_quantity : '');
            var minQuantity = minQuantityState[0];
            var setMinQuantity = minQuantityState[1];
            
            var usageLimitState = useState(rule ? rule.usage_limit : '');
            var usageLimit = usageLimitState[0];
            var setUsageLimit = usageLimitState[1];
            
            var priorityState = useState(rule ? rule.priority : 10);
            var priority = priorityState[0];
            var setPriority = priorityState[1];
            
            var bulkRangesState = useState(
                rule && rule.conditions && rule.conditions.bulk_ranges ? 
                    rule.conditions.bulk_ranges : 
                    [{ min: 1, max: '', discount: 10 }]
            );
            var bulkRanges = bulkRangesState[0];
            var setBulkRanges = bulkRangesState[1];
            
            function handleSave() {
                if (!title.trim()) {
                    alert(__('Please enter a rule title', 'discount-manager-woocommerce'));
                    return;
                }
                
                var data = {
                    title: title.trim(),
                    description: description.trim(),
                    discount_type: discountType,
                    discount_value: discountValue,
                    conditions: {
                        min_subtotal: minSubtotal ? parseFloat(minSubtotal) : null,
                        min_quantity: minQuantity ? parseInt(minQuantity) : null,
                        bulk_ranges: discountType === 'bulk' ? bulkRanges : null
                    },
                    usage_limit: usageLimit ? parseInt(usageLimit) : null,
                    priority: priority,
                    status: status
                };
                
                onSave(data);
            }
            
            function addBulkRange() {
                setBulkRanges(bulkRanges.concat([{ min: '', max: '', discount: 0 }]));
            }
            
            function updateBulkRange(index, field, value) {
                var newRanges = bulkRanges.slice();
                newRanges[index][field] = value;
                setBulkRanges(newRanges);
            }
            
            function removeBulkRange(index) {
                if (bulkRanges.length > 1) {
                    setBulkRanges(bulkRanges.filter(function(_, i) { return i !== index; }));
                }
            }
            
            return createElement('div', { className: 'dmwoo-form-container' },
                createElement('h2', null, rule ? __('Edit Rule', 'discount-manager-woocommerce') : __('Add New Rule', 'discount-manager-woocommerce')),
                
                // Basic Info
                createElement('div', { className: 'dmwoo-form-section' },
                    createElement('h3', null, __('Basic Information', 'discount-manager-woocommerce')),
                    createElement(TextControl, {
                        label: __('Rule Title', 'discount-manager-woocommerce'),
                        value: title,
                        onChange: setTitle,
                        placeholder: __('e.g., Summer Sale 20% Off', 'discount-manager-woocommerce')
                    }),
                    createElement(TextControl, {
                        label: __('Description (Optional)', 'discount-manager-woocommerce'),
                        value: description,
                        onChange: setDescription,
                        placeholder: __('Brief description of this discount rule', 'discount-manager-woocommerce')
                    }),
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
                    createElement(SelectControl, {
                        label: __('Discount Type', 'discount-manager-woocommerce'),
                        value: discountType,
                        options: [
                            { label: __('Percentage Discount', 'discount-manager-woocommerce'), value: 'percentage' },
                            { label: __('Bulk Discount', 'discount-manager-woocommerce'), value: 'bulk' }
                        ],
                        onChange: setDiscountType
                    }),
                    
                    discountType === 'percentage' && createElement(TextControl, {
                        label: __('Discount Percentage', 'discount-manager-woocommerce'),
                        type: 'number',
                        value: discountValue,
                        onChange: function(value) { setDiscountValue(parseFloat(value) || 0); },
                        min: 0,
                        max: 100,
                        step: 0.01
                    }),
                    
                    discountType === 'bulk' && createElement('div', { className: 'dmwoo-bulk-section' },
                        createElement('h4', null, __('Bulk Discount Ranges', 'discount-manager-woocommerce')),
                        bulkRanges.map(function(range, index) {
                            return createElement('div', { key: index, className: 'dmwoo-bulk-range' },
                                createElement(TextControl, {
                                    label: __('Min Qty', 'discount-manager-woocommerce'),
                                    type: 'number',
                                    value: range.min,
                                    onChange: function(value) { updateBulkRange(index, 'min', parseInt(value) || 0); }
                                }),
                                createElement(TextControl, {
                                    label: __('Max Qty', 'discount-manager-woocommerce'),
                                    type: 'number',
                                    value: range.max,
                                    onChange: function(value) { updateBulkRange(index, 'max', value ? parseInt(value) : ''); },
                                    placeholder: __('Unlimited', 'discount-manager-woocommerce')
                                }),
                                createElement(TextControl, {
                                    label: __('Discount %', 'discount-manager-woocommerce'),
                                    type: 'number',
                                    value: range.discount,
                                    onChange: function(value) { updateBulkRange(index, 'discount', parseFloat(value) || 0); },
                                    min: 0,
                                    max: 100
                                }),
                                bulkRanges.length > 1 && createElement(Button, {
                                    variant: 'link',
                                    isDestructive: true,
                                    onClick: function() { removeBulkRange(index); }
                                }, __('Remove', 'discount-manager-woocommerce'))
                            );
                        }),
                        createElement(Button, {
                            variant: 'secondary',
                            onClick: addBulkRange
                        }, __('Add Range', 'discount-manager-woocommerce'))
                    )
                ),
                
                // Conditions
                createElement('div', { className: 'dmwoo-form-section' },
                    createElement('h3', null, __('Conditions (Optional)', 'discount-manager-woocommerce')),
                    createElement('div', { className: 'dmwoo-conditions-grid' },
                        createElement(TextControl, {
                            label: __('Minimum Cart Subtotal', 'discount-manager-woocommerce'),
                            type: 'number',
                            value: minSubtotal,
                            onChange: setMinSubtotal,
                            placeholder: __('No minimum', 'discount-manager-woocommerce')
                        }),
                        createElement(TextControl, {
                            label: __('Minimum Quantity', 'discount-manager-woocommerce'),
                            type: 'number',
                            value: minQuantity,
                            onChange: setMinQuantity,
                            placeholder: __('No minimum', 'discount-manager-woocommerce')
                        })
                    )
                ),
                
                // Advanced
                createElement('div', { className: 'dmwoo-form-section' },
                    createElement('h3', null, __('Advanced Settings', 'discount-manager-woocommerce')),
                    createElement('div', { className: 'dmwoo-advanced-grid' },
                        createElement(TextControl, {
                            label: __('Usage Limit', 'discount-manager-woocommerce'),
                            type: 'number',
                            value: usageLimit,
                            onChange: setUsageLimit,
                            placeholder: __('Unlimited', 'discount-manager-woocommerce')
                        }),
                        createElement(TextControl, {
                            label: __('Priority', 'discount-manager-woocommerce'),
                            type: 'number',
                            value: priority,
                            onChange: function(value) { setPriority(parseInt(value) || 10); },
                            help: __('Lower numbers = higher priority', 'discount-manager-woocommerce')
                        })
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
        
        // Main App Component
        function App() {
            var rulesState = useState([]);
            var rules = rulesState[0];
            var setRules = rulesState[1];
            
            var loadingState = useState(true);
            var loading = loadingState[0];
            var setLoading = loadingState[1];
            
            var editingRuleState = useState(null);
            var editingRule = editingRuleState[0];
            var setEditingRule = editingRuleState[1];
            
            var showFormState = useState(false);
            var showForm = showFormState[0];
            var setShowForm = showFormState[1];
            
            var noticeState = useState(null);
            var notice = noticeState[0];
            var setNotice = noticeState[1];
            
            useEffect(function() {
                loadRules();
            }, []);
            
            function loadRules() {
                console.log('DMWOO: Loading rules...');
                wp.apiFetch({ path: '/dmwoo/v1/rules' })
                    .then(function(response) {
                        console.log('DMWOO: Rules loaded:', response);
                        setRules(response || []);
                        setLoading(false);
                    })
                    .catch(function(error) {
                        console.error('DMWOO: Error loading rules:', error);
                        setNotice({ type: 'error', message: __('Failed to load rules', 'discount-manager-woocommerce') });
                        setLoading(false);
                    });
            }
            
            function handleSaveRule(ruleData) {
                console.log('DMWOO: Saving rule:', ruleData);
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
                                __('Rule updated successfully', 'discount-manager-woocommerce') :
                                __('Rule created successfully', 'discount-manager-woocommerce')
                        });
                        loadRules();
                        setShowForm(false);
                        setEditingRule(null);
                    })
                    .catch(function(error) {
                        console.error('DMWOO: Error saving rule:', error);
                        setNotice({ type: 'error', message: __('Failed to save rule', 'discount-manager-woocommerce') });
                    });
            }
            
            function handleDeleteRule(ruleId) {
                if (!confirm(__('Are you sure you want to delete this rule?', 'discount-manager-woocommerce'))) {
                    return;
                }
                
                wp.apiFetch({
                    path: '/dmwoo/v1/rules/' + ruleId,
                    method: 'DELETE'
                })
                .then(function() {
                    setNotice({ type: 'success', message: __('Rule deleted successfully', 'discount-manager-woocommerce') });
                    loadRules();
                })
                .catch(function(error) {
                    console.error('DMWOO: Error deleting rule:', error);
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
                    console.error('DMWOO: Error toggling status:', error);
                    setNotice({ type: 'error', message: __('Failed to update rule status', 'discount-manager-woocommerce') });
                });
            }
            
            if (loading) {
                return createElement('div', { className: 'dmwoo-loading' }, 
                    __('Loading discount rules...', 'discount-manager-woocommerce')
                );
            }
            
            return createElement('div', { className: 'dmwoo-app' },
                notice && createElement(Notice, {
                    status: notice.type,
                    onRemove: function() { setNotice(null); },
                    isDismissible: true
                }, notice.message),
                
                showForm ? 
                    createElement(RuleForm, {
                        rule: editingRule,
                        onSave: handleSaveRule,
                        onCancel: function() {
                            setShowForm(false);
                            setEditingRule(null);
                        }
                    }) :
                    createElement(RulesList, {
                        rules: rules,
                        onEdit: function(rule) {
                            setEditingRule(rule);
                            setShowForm(true);
                        },
                        onDelete: handleDeleteRule,
                        onAdd: function() {
                            setEditingRule(null);
                            setShowForm(true);
                        },
                        onToggleStatus: handleToggleStatus
                    })
            );
        }
        
        // Render the app
        var container = document.getElementById('dmwoo-admin-root');
        if (container) {
            console.log('DMWOO: Rendering React app...');
            wp.element.render(createElement(App), container);
            console.log('DMWOO: React app rendered successfully');
        } else {
            console.error('DMWOO: Container not found');
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