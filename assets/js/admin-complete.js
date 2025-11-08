/**
 * Complete Admin App - WordPress Native React
 */
(function() {
    const { createElement, useState, useEffect } = wp.element;
    const { Button, Card, CardBody, TextControl, SelectControl, ToggleControl, DateTimePicker, Modal, Notice } = wp.components;
    const { __ } = wp.i18n;

    // Rule Form Component
    const RuleForm = ({ rule, onSave, onCancel, products, categories }) => {
        const [formData, setFormData] = useState({
            title: rule?.title || '',
            description: rule?.description || '',
            discount_type: rule?.discount_type || 'percentage',
            discount_value: rule?.discount_value || 0,
            conditions: rule?.conditions || {},
            filters: rule?.filters || {},
            date_from: rule?.date_from || '',
            date_to: rule?.date_to || '',
            usage_limit: rule?.usage_limit || '',
            priority: rule?.priority || 10,
            status: rule?.status || 'active'
        });

        const [bulkRanges, setBulkRanges] = useState(
            rule?.conditions?.bulk_ranges || [{ min: 1, max: '', discount: 0 }]
        );

        const updateFormData = (field, value) => {
            setFormData(prev => ({ ...prev, [field]: value }));
        };

        const updateCondition = (field, value) => {
            setFormData(prev => ({
                ...prev,
                conditions: { ...prev.conditions, [field]: value }
            }));
        };

        const updateFilter = (field, value) => {
            setFormData(prev => ({
                ...prev,
                filters: { ...prev.filters, [field]: value }
            }));
        };

        const addBulkRange = () => {
            setBulkRanges([...bulkRanges, { min: '', max: '', discount: 0 }]);
        };

        const updateBulkRange = (index, field, value) => {
            const newRanges = [...bulkRanges];
            newRanges[index][field] = value;
            setBulkRanges(newRanges);
        };

        const removeBulkRange = (index) => {
            setBulkRanges(bulkRanges.filter((_, i) => i !== index));
        };

        const handleSubmit = () => {
            if (!formData.title.trim()) {
                alert(__('Please enter a rule title', 'discount-manager-woocommerce'));
                return;
            }

            const data = { ...formData };
            if (formData.discount_type === 'bulk') {
                data.conditions = { ...data.conditions, bulk_ranges: bulkRanges };
            }
            onSave(data);
        };

        return createElement('div', { className: 'dmwoo-rule-form' },
            createElement('h2', null, rule ? __('Edit Rule', 'discount-manager-woocommerce') : __('Add New Rule', 'discount-manager-woocommerce')),
            
            // Basic Information
            createElement('div', { className: 'dmwoo-form-section' },
                createElement('h3', null, __('Basic Information', 'discount-manager-woocommerce')),
                
                createElement(TextControl, {
                    label: __('Rule Title', 'discount-manager-woocommerce'),
                    value: formData.title,
                    onChange: (value) => updateFormData('title', value),
                    placeholder: __('e.g., Summer Sale 20% Off', 'discount-manager-woocommerce')
                }),

                createElement(TextControl, {
                    label: __('Description', 'discount-manager-woocommerce'),
                    value: formData.description,
                    onChange: (value) => updateFormData('description', value),
                    placeholder: __('Optional description for this rule', 'discount-manager-woocommerce')
                }),

                createElement(SelectControl, {
                    label: __('Status', 'discount-manager-woocommerce'),
                    value: formData.status,
                    options: [
                        { label: __('Active', 'discount-manager-woocommerce'), value: 'active' },
                        { label: __('Inactive', 'discount-manager-woocommerce'), value: 'inactive' }
                    ],
                    onChange: (value) => updateFormData('status', value)
                })
            ),

            // Discount Configuration
            createElement('div', { className: 'dmwoo-form-section' },
                createElement('h3', null, __('Discount Configuration', 'discount-manager-woocommerce')),
                
                createElement(SelectControl, {
                    label: __('Discount Type', 'discount-manager-woocommerce'),
                    value: formData.discount_type,
                    options: [
                        { label: __('Percentage Discount', 'discount-manager-woocommerce'), value: 'percentage' },
                        { label: __('Bulk Discount', 'discount-manager-woocommerce'), value: 'bulk' }
                    ],
                    onChange: (value) => updateFormData('discount_type', value)
                }),

                formData.discount_type === 'percentage' && createElement(TextControl, {
                    label: __('Discount Percentage', 'discount-manager-woocommerce'),
                    type: 'number',
                    value: formData.discount_value,
                    onChange: (value) => updateFormData('discount_value', parseFloat(value) || 0),
                    min: 0,
                    max: 100,
                    step: 0.01
                }),

                formData.discount_type === 'bulk' && createElement('div', { className: 'dmwoo-bulk-ranges' },
                    createElement('h4', null, __('Bulk Discount Ranges', 'discount-manager-woocommerce')),
                    bulkRanges.map((range, index) => 
                        createElement('div', { key: index, className: 'dmwoo-bulk-range' },
                            createElement(TextControl, {
                                label: __('Min Quantity', 'discount-manager-woocommerce'),
                                type: 'number',
                                value: range.min,
                                onChange: (value) => updateBulkRange(index, 'min', parseInt(value) || 0)
                            }),
                            createElement(TextControl, {
                                label: __('Max Quantity', 'discount-manager-woocommerce'),
                                type: 'number',
                                value: range.max,
                                onChange: (value) => updateBulkRange(index, 'max', value ? parseInt(value) : ''),
                                placeholder: __('Leave empty for unlimited', 'discount-manager-woocommerce')
                            }),
                            createElement(TextControl, {
                                label: __('Discount %', 'discount-manager-woocommerce'),
                                type: 'number',
                                value: range.discount,
                                onChange: (value) => updateBulkRange(index, 'discount', parseFloat(value) || 0),
                                min: 0,
                                max: 100,
                                step: 0.01
                            }),
                            bulkRanges.length > 1 && createElement(Button, {\n                                variant: 'link',\n                                isDestructive: true,\n                                onClick: () => removeBulkRange(index)\n                            }, __('Remove', 'discount-manager-woocommerce'))\n                        )\n                    ),\n                    createElement(Button, {\n                        variant: 'secondary',\n                        onClick: addBulkRange\n                    }, __('Add Range', 'discount-manager-woocommerce'))\n                )\n            ),\n\n            // Conditions\n            createElement('div', { className: 'dmwoo-form-section' },\n                createElement('h3', null, __('Conditions', 'discount-manager-woocommerce')),\n                \n                createElement(TextControl, {\n                    label: __('Minimum Cart Subtotal', 'discount-manager-woocommerce'),\n                    type: 'number',\n                    value: formData.conditions.min_subtotal || '',\n                    onChange: (value) => updateCondition('min_subtotal', value ? parseFloat(value) : ''),\n                    placeholder: __('Leave empty for no minimum', 'discount-manager-woocommerce')\n                }),\n\n                createElement(TextControl, {\n                    label: __('Minimum Quantity', 'discount-manager-woocommerce'),\n                    type: 'number',\n                    value: formData.conditions.min_quantity || '',\n                    onChange: (value) => updateCondition('min_quantity', value ? parseInt(value) : ''),\n                    placeholder: __('Leave empty for no minimum', 'discount-manager-woocommerce')\n                })\n            ),\n\n            // Date Range\n            createElement('div', { className: 'dmwoo-form-section' },\n                createElement('h3', null, __('Date Range', 'discount-manager-woocommerce')),\n                \n                createElement('div', { className: 'dmwoo-date-range' },\n                    createElement('div', null,\n                        createElement('label', null, __('Start Date', 'discount-manager-woocommerce')),\n                        createElement('input', {\n                            type: 'datetime-local',\n                            value: formData.date_from,\n                            onChange: (e) => updateFormData('date_from', e.target.value)\n                        })\n                    ),\n                    createElement('div', null,\n                        createElement('label', null, __('End Date', 'discount-manager-woocommerce')),\n                        createElement('input', {\n                            type: 'datetime-local',\n                            value: formData.date_to,\n                            onChange: (e) => updateFormData('date_to', e.target.value)\n                        })\n                    )\n                )\n            ),\n\n            // Usage Limits\n            createElement('div', { className: 'dmwoo-form-section' },\n                createElement('h3', null, __('Usage Limits', 'discount-manager-woocommerce')),\n                \n                createElement(TextControl, {\n                    label: __('Usage Limit', 'discount-manager-woocommerce'),\n                    type: 'number',\n                    value: formData.usage_limit,\n                    onChange: (value) => updateFormData('usage_limit', value ? parseInt(value) : ''),\n                    placeholder: __('Leave empty for unlimited usage', 'discount-manager-woocommerce')\n                }),\n\n                createElement(TextControl, {\n                    label: __('Priority', 'discount-manager-woocommerce'),\n                    type: 'number',\n                    value: formData.priority,\n                    onChange: (value) => updateFormData('priority', parseInt(value) || 10),\n                    help: __('Lower numbers = higher priority', 'discount-manager-woocommerce')\n                })\n            ),\n\n            // Form Actions\n            createElement('div', { className: 'dmwoo-form-actions' },\n                createElement(Button, {\n                    variant: 'primary',\n                    onClick: handleSubmit\n                }, rule ? __('Update Rule', 'discount-manager-woocommerce') : __('Create Rule', 'discount-manager-woocommerce')),\n                createElement(Button, {\n                    variant: 'secondary',\n                    onClick: onCancel\n                }, __('Cancel', 'discount-manager-woocommerce'))\n            )\n        );\n    };\n\n    // Rules List Component\n    const RulesList = ({ rules, onEdit, onDelete, onAdd, onToggleStatus }) => {\n        return createElement('div', { className: 'dmwoo-rules-list' },\n            createElement('div', { className: 'dmwoo-rules-header' },\n                createElement('h2', null, __('Discount Rules', 'discount-manager-woocommerce')),\n                createElement(Button, {\n                    variant: 'primary',\n                    onClick: onAdd\n                }, __('Add New Rule', 'discount-manager-woocommerce'))\n            ),\n\n            rules.length === 0 ? \n                createElement('div', { className: 'dmwoo-empty-state' },\n                    createElement('p', null, __('No discount rules found.', 'discount-manager-woocommerce')),\n                    createElement('p', null, __('Create your first discount rule to get started!', 'discount-manager-woocommerce'))\n                ) :\n                createElement('div', { className: 'dmwoo-rules-table' },\n                    createElement('table', { className: 'wp-list-table widefat fixed striped' },\n                        createElement('thead', null,\n                            createElement('tr', null,\n                                createElement('th', null, __('Title', 'discount-manager-woocommerce')),\n                                createElement('th', null, __('Type', 'discount-manager-woocommerce')),\n                                createElement('th', null, __('Value', 'discount-manager-woocommerce')),\n                                createElement('th', null, __('Status', 'discount-manager-woocommerce')),\n                                createElement('th', null, __('Usage', 'discount-manager-woocommerce')),\n                                createElement('th', null, __('Actions', 'discount-manager-woocommerce'))\n                            )\n                        ),\n                        createElement('tbody', null,\n                            rules.map(rule => \n                                createElement('tr', { key: rule.id },\n                                    createElement('td', null,\n                                        createElement('strong', null, rule.title),\n                                        rule.description && createElement('div', { className: 'dmwoo-rule-description' }, rule.description)\n                                    ),\n                                    createElement('td', null, \n                                        rule.discount_type === 'percentage' ? \n                                            __('Percentage', 'discount-manager-woocommerce') :\n                                            __('Bulk Discount', 'discount-manager-woocommerce')\n                                    ),\n                                    createElement('td', null,\n                                        rule.discount_type === 'percentage' ? \n                                            `${rule.discount_value}%` :\n                                            `${rule.conditions?.bulk_ranges?.length || 0} ranges`\n                                    ),\n                                    createElement('td', null,\n                                        createElement('button', {\n                                            className: `dmwoo-status-toggle dmwoo-status-${rule.status}`,\n                                            onClick: () => onToggleStatus(rule)\n                                        }, rule.status === 'active' ? __('Active', 'discount-manager-woocommerce') : __('Inactive', 'discount-manager-woocommerce'))\n                                    ),\n                                    createElement('td', null, `${rule.usage_count || 0}/${rule.usage_limit || '∞'}`),\n                                    createElement('td', null,\n                                        createElement('div', { className: 'dmwoo-rule-actions' },\n                                            createElement(Button, {\n                                                variant: 'secondary',\n                                                size: 'small',\n                                                onClick: () => onEdit(rule)\n                                            }, __('Edit', 'discount-manager-woocommerce')),\n                                            createElement(Button, {\n                                                variant: 'link',\n                                                isDestructive: true,\n                                                size: 'small',\n                                                onClick: () => onDelete(rule.id)\n                                            }, __('Delete', 'discount-manager-woocommerce'))\n                                        )\n                                    )\n                                )\n                            )\n                        )\n                    )\n                )\n        );\n    };\n\n    // Main Admin App\n    const AdminApp = () => {\n        const [rules, setRules] = useState([]);\n        const [loading, setLoading] = useState(true);\n        const [editingRule, setEditingRule] = useState(null);\n        const [showForm, setShowForm] = useState(false);\n        const [notice, setNotice] = useState(null);\n\n        useEffect(() => {\n            loadRules();\n        }, []);\n\n        const loadRules = async () => {\n            try {\n                const response = await wp.apiFetch({ path: '/dmwoo/v1/rules' });\n                setRules(response || []);\n            } catch (error) {\n                setNotice({ type: 'error', message: __('Failed to load rules', 'discount-manager-woocommerce') });\n            } finally {\n                setLoading(false);\n            }\n        };\n\n        const handleSaveRule = async (ruleData) => {\n            try {\n                if (editingRule) {\n                    await wp.apiFetch({\n                        path: `/dmwoo/v1/rules/${editingRule.id}`,\n                        method: 'PUT',\n                        data: ruleData\n                    });\n                    setNotice({ type: 'success', message: __('Rule updated successfully', 'discount-manager-woocommerce') });\n                } else {\n                    await wp.apiFetch({\n                        path: '/dmwoo/v1/rules',\n                        method: 'POST',\n                        data: ruleData\n                    });\n                    setNotice({ type: 'success', message: __('Rule created successfully', 'discount-manager-woocommerce') });\n                }\n                loadRules();\n                setShowForm(false);\n                setEditingRule(null);\n            } catch (error) {\n                setNotice({ type: 'error', message: __('Failed to save rule', 'discount-manager-woocommerce') });\n            }\n        };\n\n        const handleDeleteRule = async (ruleId) => {\n            if (!confirm(__('Are you sure you want to delete this rule?', 'discount-manager-woocommerce'))) {\n                return;\n            }\n\n            try {\n                await wp.apiFetch({\n                    path: `/dmwoo/v1/rules/${ruleId}`,\n                    method: 'DELETE'\n                });\n                setNotice({ type: 'success', message: __('Rule deleted successfully', 'discount-manager-woocommerce') });\n                loadRules();\n            } catch (error) {\n                setNotice({ type: 'error', message: __('Failed to delete rule', 'discount-manager-woocommerce') });\n            }\n        };\n\n        const handleToggleStatus = async (rule) => {\n            const newStatus = rule.status === 'active' ? 'inactive' : 'active';\n            try {\n                await wp.apiFetch({\n                    path: `/dmwoo/v1/rules/${rule.id}`,\n                    method: 'PUT',\n                    data: { ...rule, status: newStatus }\n                });\n                loadRules();\n            } catch (error) {\n                setNotice({ type: 'error', message: __('Failed to update rule status', 'discount-manager-woocommerce') });\n            }\n        };\n\n        if (loading) {\n            return createElement('div', { className: 'dmwoo-loading' }, \n                __('Loading...', 'discount-manager-woocommerce')\n            );\n        }\n\n        return createElement('div', { className: 'dmwoo-admin' },\n            notice && createElement(Notice, {\n                status: notice.type,\n                onRemove: () => setNotice(null),\n                isDismissible: true\n            }, notice.message),\n\n            showForm ? \n                createElement(RuleForm, {\n                    rule: editingRule,\n                    onSave: handleSaveRule,\n                    onCancel: () => {\n                        setShowForm(false);\n                        setEditingRule(null);\n                    }\n                }) :\n                createElement(RulesList, {\n                    rules: rules,\n                    onEdit: (rule) => {\n                        setEditingRule(rule);\n                        setShowForm(true);\n                    },\n                    onDelete: handleDeleteRule,\n                    onAdd: () => {\n                        setEditingRule(null);\n                        setShowForm(true);\n                    },\n                    onToggleStatus: handleToggleStatus\n                })\n        );\n    };\n\n    // Initialize\n    function initCompleteApp() {\n        const container = document.getElementById('dmwoo-admin-root');\n        if (!container) return;\n\n        if (typeof wp === 'undefined' || typeof wp.element === 'undefined') {\n            container.innerHTML = '<div class=\"notice notice-error\"><p>WordPress React not available. <a href=\"?page=discount-manager&fallback=1\">Use simple interface</a></p></div>';\n            return;\n        }\n\n        try {\n            wp.element.render(createElement(AdminApp), container);\n        } catch (error) {\n            console.error('DMWOO: Error rendering app:', error);\n            container.innerHTML = '<div class=\"notice notice-error\"><p>Error loading interface: ' + error.message + '</p></div>';\n        }\n    }\n\n    if (document.readyState === 'loading') {\n        document.addEventListener('DOMContentLoaded', initCompleteApp);\n    } else {\n        initCompleteApp();\n    }\n})();