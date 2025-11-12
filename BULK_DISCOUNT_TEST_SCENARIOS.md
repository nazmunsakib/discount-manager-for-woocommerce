# Bulk Discount Test Scenarios

## Test Scenario 1: Individual Product Bulk Discount
**Setup:**
- Rule Type: Bulk Discount
- Bulk Operator: Individual Product
- Apply to: All Products
- Ranges:
  - Qty 1-2: 10% off
  - Qty 3-5: 15% off
  - Qty 6+: 20% off

**Test Case:**
- Add 2x Product A (৳100 each) → Each gets 10% off = ৳90 each
- Add 4x Product B (৳200 each) → Each gets 15% off = ৳170 each
- Add 7x Product C (৳50 each) → Each gets 20% off = ৳40 each

**Expected Result:**
- Product A: 2 × ৳90 = ৳180
- Product B: 4 × ৳170 = ৳680
- Product C: 7 × ৳40 = ৳280
- Total: ৳1,140

---

## Test Scenario 2: Cumulative Bulk Discount
**Setup:**
- Rule Type: Bulk Discount
- Bulk Operator: Cumulative
- Apply to: Category "T-Shirts"
- Ranges:
  - Qty 1-5: 10% off
  - Qty 6-10: 20% off
  - Qty 11+: 30% off

**Test Case:**
- Add 3x Red T-Shirt (৳500 each)
- Add 4x Blue T-Shirt (৳600 each)
- Total matching quantity: 3 + 4 = 7 items

**Expected Result:**
- All 7 items get 20% discount (because total = 7, which falls in 6-10 range)
- Red T-Shirt: 3 × (৳500 - 20%) = 3 × ৳400 = ৳1,200
- Blue T-Shirt: 4 × (৳600 - 20%) = 4 × ৳480 = ৳1,920
- Total: ৳3,120

---

## Test Scenario 3: Fixed Price Bulk Discount
**Setup:**
- Rule Type: Bulk Discount
- Bulk Operator: Individual Product
- Apply to: All Products
- Ranges:
  - Qty 1-9: 10% off
  - Qty 10-49: Fixed Price ৳50
  - Qty 50+: Fixed Price ৳40

**Test Case:**
- Add 5x Product (৳100 each) → 10% off = ৳90 each
- Add 15x Product (৳100 each) → Fixed price ৳50 each
- Add 60x Product (৳100 each) → Fixed price ৳40 each

**Expected Result:**
- 5 items: 5 × ৳90 = ৳450
- 15 items: 15 × ৳50 = ৳750
- 60 items: 60 × ৳40 = ৳2,400

---

## Test Scenario 4: Cumulative with Product Filter
**Setup:**
- Rule Type: Bulk Discount
- Bulk Operator: Cumulative
- Apply to: Specific Products (Product A, Product B, Product C)
- Filter Method: Include
- Ranges:
  - Qty 1-10: 5% off
  - Qty 11-20: 10% off
  - Qty 21+: 15% off

**Test Case:**
- Add 8x Product A (৳100 each) - matches filter
- Add 7x Product B (৳150 each) - matches filter
- Add 5x Product D (৳200 each) - does NOT match filter
- Total matching: 8 + 7 = 15 items

**Expected Result:**
- Product A & B get 10% discount (total matching = 15, falls in 11-20 range)
- Product A: 8 × (৳100 - 10%) = 8 × ৳90 = ৳720
- Product B: 7 × (৳150 - 10%) = 7 × ৳135 = ৳945
- Product D: 5 × ৳200 = ৳1,000 (no discount, doesn't match filter)
- Total: ৳2,665

---

## Test Scenario 5: Exclude Filter with Cumulative
**Setup:**
- Rule Type: Bulk Discount
- Bulk Operator: Cumulative
- Apply to: Specific Products (Product X)
- Filter Method: Exclude
- Ranges:
  - Qty 1-5: 10% off
  - Qty 6+: 20% off

**Test Case:**
- Add 3x Product A (৳100 each) - matches (not excluded)
- Add 4x Product B (৳150 each) - matches (not excluded)
- Add 2x Product X (৳200 each) - does NOT match (excluded)
- Total matching: 3 + 4 = 7 items

**Expected Result:**
- Product A & B get 20% discount (total = 7, falls in 6+ range)
- Product A: 3 × (৳100 - 20%) = 3 × ৳80 = ৳240
- Product B: 4 × (৳150 - 20%) = 4 × ৳120 = ৳480
- Product X: 2 × ৳200 = ৳400 (no discount, excluded)
- Total: ৳1,120

---

## Verification Checklist

### Frontend Display:
- [ ] Bulk pricing table shows on product pages
- [ ] Table displays correct quantity ranges
- [ ] Discount types shown correctly (%, fixed, price)
- [ ] Custom labels display when set
- [ ] Final prices calculated correctly

### Cart Behavior:
- [ ] Individual mode: Each product's quantity determines its discount
- [ ] Cumulative mode: Total matching products determine discount
- [ ] Prices update correctly when quantities change
- [ ] Multiple rules don't conflict

### Admin Interface:
- [ ] Bulk operator dropdown works
- [ ] Fixed price option available
- [ ] Rules list shows operator type
- [ ] Ranges can be added/removed
- [ ] Save/update works correctly

### Edge Cases:
- [ ] Empty cart handles correctly
- [ ] Single item in cart
- [ ] Quantity exactly at range boundary (e.g., qty = 10 for 6-10 range)
- [ ] Unlimited max (null) works
- [ ] Zero or negative prices prevented
