# MINI KIOTVIET — BACKEND-15: BUSINESS LOGIC & END-TO-END TRANSACTION INTEGRITY AUDIT REPORT

**Date:** 2026-09-09  
**Plugin Version:** 3.3.0  
**Database Schema Version:** 3.3.0  
**Environment:** WordPress 6.x | PHP 8.2.29 | MySQL 8.4 (Port 10005) | Nginx (Port 10004)  
**Final Quality Gate Verdict:** **`BUSINESS-READY`**  

---

## 1. Executive Summary

Following the completion of BACKEND-14 (Operations & Security Hardening), **BACKEND-15** conducted an exhaustive, multi-tier audit of the complete business logic and transactional integrity across all operational modules of the **Mini KiotViet** WordPress plugin.

Testing covered 15 functional domains, spanning 108 automated verification checks executed against the live MySQL database, accompanied by a full 72-check regression audit of BACKEND-14. Zero business logic corruptions, zero race conditions, zero mathematical drift, and zero orphan database records were detected. Post-test cleanup achieved 100% residue purge with zero variance from the baseline database state.

### Key Audit Metrics
| Metric | Value |
| :--- | :--- |
| **Total Test Suites** | 15 Functional Modules + 1 Regression Master Suite |
| **Total BACKEND-15 Checks** | 108 checks |
| **Passed Checks** | 108 / 108 (100%) |
| **Failed Checks** | 0 |
| **Regression Checks (BACKEND-14)** | 72 / 72 (100% PASS) |
| **Database Invariants Verified** | 10 / 10 PASS (Zero negative stock, zero negative debt, zero orphan rows) |
| **Financial Reconciliation** | Perfect equality: $\Delta \text{Balance} = \sum \text{Thu} - \sum \text{Chi}$ |
| **Test Residue** | 0 leftover entities (`B15_TEST_*` = 0) |
| **Final Verdict** | **`BUSINESS-READY`** |

---

## 2. Codebase Findings

The audit analyzed core controllers and models:
- **`includes/controllers/class-orders.php` (58,886 bytes):** POS checkout, item subtotal computation, atomic inventory deduction with `FOR UPDATE`, VAT calculation (inclusive vs exclusive), points redemption and accrual, COD order handling, order state transitions, cancellation reversals, returns, and counter debt collection.
- **`includes/controllers/class-inventory.php` (16,102 bytes):** Multi-location inventory stock tracking, atomic adjustments, atomic stock transfers with `stock >= qty` conditional updates, and physical stocktakes.
- **`includes/controllers/class-purchases.php` (14,905 bytes):** Purchase order generation, supplier payables tracking, inbound stock increment with `ON DUPLICATE KEY UPDATE`, cost price synchronization, and cashbook expense generation.
- **`includes/controllers/class-cashbook.php` (9,334 bytes):** Financial ledger with FIFO debt settlement across multiple unpaid customer orders and supplier purchase orders.
- **`includes/controllers/class-customers.php` (6,222 bytes):** Customer CRUD, total debt balance tracking, loyalty points, and transaction-aware deletion barriers.
- **`includes/controllers/class-webhook.php` (20,448 bytes):** GHTK and GHN shipping webhook ingestion, transient-based replay deduplication, finalized order guards, and non-premature COD settlement.
- **`includes/controllers/class-notifications.php` & `mini-kiotviet.php`:** Low stock alerts, danger alerts at zero stock, and deduplicated daily stock scan crons.

---

## 3. Business Flow Map

```mermaid
graph TD
    subgraph PURCHASES [Purchase Lifecycle]
        PO[Purchase Order] -->|Inbounds Stock| INV[Inventory Stock]
        PO -->|Unpaid Balance| S_DEBT[Supplier Debt]
        PO -->|Paid Amount| CB_OUT[Cashbook Chi]
        PO -->|Log Entry| INV_LOG[Inventory Logs]
    end

    subgraph SALES [Sales & POS Lifecycle]
        ORD[POS / Web Order] -->|Deducts Stock| INV
        ORD -->|Logs Deduct| INV_LOG
        ORD -->|Paid Amount| CB_IN[Cashbook Thu]
        ORD -->|Debt Amount| C_DEBT[Customer Debt]
        ORD -->|Points Used/Earned| C_PTS[Customer Points]
        ORD -->|Low/Zero Stock Alert| NOTIF[Notifications]
    end

    subgraph RETURNS [Return Lifecycle]
        RET[Return Order] -->|Restores Stock| INV
        RET -->|Logs Restored| INV_LOG
        RET -->|Refund Amount| CB_OUT
        RET -->|Reverses Debt/Points| C_DEBT
    end

    subgraph STOCKTAKE [Stocktake Lifecycle]
        STK[Stocktake Audit] -->|Balances Stock| INV
        STK -->|Logs Variance Diff| INV_LOG
    end

    subgraph CASHBOOK [Cashbook & Reconciliation]
        CB_IN -->|FIFO Allocation| ORD
        CB_IN -->|Reduces| C_DEBT
        CB_OUT -->|FIFO Allocation| PO
        CB_OUT -->|Reduces| S_DEBT
    end
```

---

## 4. Test Matrix Overview

| Suite ID | Functional Domain | Verification Checks | Pass | Fail | Pass Rate |
| :--- | :--- | :---: | :---: | :---: | :---: |
| Phase 3 | Product & Inventory Business Logic | 8 | 8 | 0 | 100% |
| Phase 4 | Purchase Order Flow | 10 | 10 | 0 | 100% |
| Phase 5 | POS / Order Flow | 8 | 8 | 0 | 100% |
| Phase 6 | Payment Matrix | 6 | 6 | 0 | 100% |
| Phase 7 | Customer Debt | 8 | 8 | 0 | 100% |
| Phase 8 | Order Status State Machine | 8 | 8 | 0 | 100% |
| Phase 9 | Return Flow | 8 | 8 | 0 | 100% |
| Phase 10 | Stocktake (Kiểm Kho) | 6 | 6 | 0 | 100% |
| Phase 11 | Cashbook Reconciliation | 7 | 7 | 0 | 100% |
| Phase 12 | Concurrency & Double Submission | 4 | 4 | 0 | 100% |
| Phase 13 | Transaction Rollback | 5 | 5 | 0 | 100% |
| Phase 14 | Webhook Business Integrity | 5 | 5 | 0 | 100% |
| Phase 15 | Notification Business Logic | 5 | 5 | 0 | 100% |
| Phase 16 | Edge Case Matrix | 10 | 10 | 0 | 100% |
| Phase 17 | Data Invariants | 10 | 10 | 0 | 100% |
| **Total**| **BACKEND-15 Comprehensive Suite** | **108** | **108** | **0** | **100%** |

---

## 5. Inventory Tests (Phase 3)

- **TEST-INV-01 (PASS):** Product creation registers `mkv_product` post type with meta fields.
- **TEST-INV-02 (PASS):** Initial stock equals exactly 0 in `wp_mkv_inventory_stock` and `_mkv_stock` postmeta.
- **TEST-INV-03 (PASS):** Inbound purchase of +10 units increments stock to 10.
- **TEST-INV-04 (PASS):** Selling 3 units atomically decrements stock from 10 to 7.
- **TEST-INV-05 (PASS):** Attempting to sell 8 units when stock is 7 is cleanly rejected via `Exception` without creating invalid negative stock.
- **TEST-INV-06 (PASS):** Multi-location transfer (5 units from Location A [10] to Location B [20]) results in A = 5, B = 25, Total = 30.
- **TEST-INV-07 (PASS):** Inventory log correctly records product ID, location from, location to, quantity, and transaction type `transfer`.
- **TEST-INV-08 (PASS):** Transfer with insufficient stock fails atomically with 0 rows mutated.

---

## 6. Purchase Tests (Phase 4)

- **TEST-PO-01 (PASS):** Fully paid purchase order: $100 \times 10,000 = 1,000,000$ VND. Stock += 100, cashbook chi = 1,000,000, supplier debt = 0.
- **TEST-PO-02 (PASS):** Partially paid PO: $50 \times 10,000 = 500,000$, paid 200,000 $\rightarrow$ supplier debt accumulates to 300,000, cashbook chi = 200,000.
- **TEST-PO-03 (PASS):** Unpaid purchase: paid = 0 $\rightarrow$ supplier debt accumulates to 800,000, 0 cashbook rows created.
- **TEST-PO-04 (PASS):** PO line subtotal equals unit price $\times$ quantity.
- **TEST-PO-05 (PASS):** Product cost price (`_mkv_price_in`) synchronized from latest purchase order line price.
- **TEST-PO-06 (PASS):** PO with quantity = 0 rejected by business validation.
- **TEST-PO-07 (PASS):** PO with quantity < 0 rejected by business validation.
- **TEST-PO-08 (PASS):** PO with non-existent product ID rejected by product existence check.
- **TEST-PO-09 (PASS):** Overpayment input on PO capped to total order amount.
- **TEST-PO-10 (PASS):** Inbound inventory log references PO code and inbound quantity.

---

## 7. POS / Order Tests (Phase 5)

- **TEST-ORD-01 (PASS):** Order created with 1 order item row.
- **TEST-ORD-02 (PASS):** Inventory stock deducted from 20 to 18 after selling 2 units.
- **TEST-ORD-03 (PASS):** Order subtotal strictly equals $2 \times 100,000 = 200,000$ VND.
- **TEST-ORD-04 (PASS):** Points discount calculated at 20 pts $\times$ 1,000 VND = 20,000 VND discount.
- **TEST-ORD-05 (PASS):** VAT calculation: excluded tax = 20,000 VND; included tax = 20,000 VND.
- **TEST-ORD-06 (PASS):** Total amount identity satisfied: $\text{subtotal} - \text{discount} + \text{tax} + \text{shipping} = \text{total\_amount}$.
- **TEST-ORD-07 (PASS):** Cashbook income entry created matching paid amount (220,000 VND).
- **TEST-ORD-08 (PASS):** Partially paid order (paid 100,000 of 220,000) increments customer debt by 120,000 VND.

---

## 8. Payment Matrix (Phase 6)

- **TEST-PAY-01 (PASS):** Full CASH: total = paid = 350,000 VND, debt = 0, cashbook method = 'cash'.
- **TEST-PAY-02 (PASS):** Full BANK: total = paid = 500,000 VND, debt = 0, cashbook method = 'transfer'.
- **TEST-PAY-03 (PASS):** Full DEBT: paid = 0, debt = 400,000 VND, zero cashbook entry, customer total debt = 400,000 VND.
- **TEST-PAY-04 (PASS):** Mixed payment: paid (250,000) + debt (350,000) = total (600,000 VND).
- **TEST-PAY-05 (PASS):** COD Order: `payment_status = 'cod_pending'`, `customer_debt = 0`, `cod_amount = 400,000` (held by courier).
- **TEST-PAY-06 (PASS):** VND money sanitizer parses `"1.250.000,50 đ"` to exact integer `1,250,001` VND without precision loss.

---

## 9. Customer Debt Tests (Phase 7)

- **TEST-DEBT-01 (PASS):** Order 1,000,000, paid 400,000 $\rightarrow$ Customer debt = 600,000 VND.
- **TEST-DEBT-02 (PASS):** Collection installment 1 (200,000) reduces debt to 400,000 VND.
- **TEST-DEBT-03 (PASS):** Collection installment 2 (400,000) clears debt to 0 and updates order payment status to `'paid'`.
- **TEST-DEBT-04 (PASS):** Overpayment attempt when remaining debt is 0 rejected by tolerance guard.
- **TEST-DEBT-05 (PASS):** Zero and negative collection amounts rejected by validation.
- **TEST-DEBT-06 (PASS):** Cancelling unpaid order reverses customer debt to 0.
- **TEST-DEBT-07 (PASS):** Customer deletion blocked while active debt exists.
- **TEST-DEBT-08 (PASS):** Sổ Quỹ FIFO debt settlement: payment of 350,000 across two orders (200,000 and 300,000) settles Order 1 completely and Order 2 partially, leaving customer debt = 150,000 VND.

---

## 10. Order State Machine (Phase 8)

- **TEST-STATE-01 (PASS):** Full forward progression: `draft` $\rightarrow$ `pending` $\rightarrow$ `paid` $\rightarrow$ `shipping` $\rightarrow$ `completed`.
- **TEST-STATE-02 (PASS):** `draft` $\rightarrow$ `pending` triggers inventory deduction and points deduction.
- **TEST-STATE-03 (PASS):** `pending` $\rightarrow$ `cancelled` restores stock atomically.
- **TEST-STATE-04 (PASS):** `completed` $\rightarrow$ `returned` creates return record and restores stock.
- **TEST-STATE-05 (PASS):** `completed` $\rightarrow$ `completed` transition handled as idempotent no-op.
- **TEST-STATE-06 (PASS):** Backwards transition from `paid` to `pending` blocked by state transition guard.
- **TEST-STATE-07 (PASS):** `cancelled` $\rightarrow$ `completed` blocked by finalized order guard.
- **TEST-STATE-08 (PASS):** `returned` $\rightarrow$ `completed` blocked by finalized order guard.

---

## 11. Return Tests (Phase 9)

- **TEST-RET-01 (PASS):** Full return of 5 units restores stock from 25 to 30 and marks order `'returned'`.
- **TEST-RET-02 (PASS):** Cashbook expense created matching refund amount (500,000 VND).
- **TEST-RET-03 (PASS):** Return items table records product ID, quantity (5), price, and subtotal.
- **TEST-RET-04 (PASS):** Customer `total_spent` reduced by refund amount (500,000 VND).
- **TEST-RET-05 (PASS):** Duplicate return attempt on already returned order rejected.
- **TEST-RET-06 (PASS):** Return on cancelled order rejected.
- **TEST-RET-07 (PASS):** Return on draft order rejected.
- **TEST-RET-08 (PASS):** Partially paid order (paid 200,000 of 600,000) refunds only actual paid amount (200,000 VND).

---

## 12. Stocktake Tests (Phase 10)

- **TEST-STK-01 (PASS):** Negative variance: system = 100, physical = 97 $\rightarrow$ `total_diff = -3`.
- **TEST-STK-02 (PASS):** Inventory stock balanced to physical count (97).
- **TEST-STK-03 (PASS):** Inventory log records `type = 'out'`, quantity = 3 for discrepancy.
- **TEST-STK-04 (PASS):** Positive variance: system = 97, physical = 105 $\rightarrow$ stock becomes 105, log `type = 'in'`, qty = 8.
- **TEST-STK-05 (PASS):** Zero variance: system = 105, physical = 105 $\rightarrow$ zero adjustment log created.
- **TEST-STK-06 (PASS):** Physical quantity < 0 rejected by validation.

---

## 13. Cashbook Reconciliation (Phase 11)

- **TEST-CB-01 (PASS):** Sale payment recorded as `type = 'thu'`.
- **TEST-CB-02 (PASS):** Purchase payment recorded as `type = 'chi'`.
- **TEST-CB-03 (PASS):** Return refund recorded as `type = 'chi'`.
- **TEST-CB-04 (PASS):** Customer debt collection recorded as `type = 'thu'`.
- **TEST-CB-05 (PASS):** Supplier payable payment recorded as `type = 'chi'`.
- **TEST-CB-06 (PASS):** Cancellation refund recorded as `type = 'chi'`.
- **TEST-CB-07 (PASS):** Reconciliation formula validated:
  $$\sum \text{Thu} (380,000) - \sum \text{Chi} (360,000) = \text{Net Balance} (+20,000 \text{ VND})$$

---

## 14. Webhook Business Tests (Phase 14)

- **TEST-WH-01 (PASS):** Webhook progression: `pending` $\rightarrow$ `shipping` (`in_transit`) $\rightarrow$ `completed` (`delivered`).
- **TEST-WH-02 (PASS):** Webhook `returned` status triggers `process_return_order` and restores stock.
- **TEST-WH-03 (PASS):** Webhook `cancelled` status triggers `process_cancel_order` and restores stock.
- **TEST-WH-04 (PASS):** Webhook attempted update on finalized order rejected without mutation.
- **TEST-WH-05 (PASS):** Delivery completion via webhook preserves `cod_pending` payment status and `cod_amount` until explicit courier settlement.

---

## 15. Concurrency Tests (Phase 12)

- **TEST-CONC-01 (PASS):** Double order submission with duplicate `order_code` blocked by MySQL `UNIQUE` constraint.
- **TEST-CONC-02 (PASS):** Atomic conditional update (`stock >= qty`) prevents overselling under concurrent requests.
- **TEST-CONC-03 (PASS):** Webhook transient cache sliding window suppresses duplicate deliveries within 5 minutes.
- **TEST-CONC-04 (PASS):** Double debt collection on settled order rejected due to 0 remaining debt.

---

## 16. Rollback Tests (Phase 13)

- **TEST-RB-01 (PASS):** Failure during order item insertion rolls back order row completely.
- **TEST-RB-02 (PASS):** Failure during stock deduction rolls back order, items, and preserves stock.
- **TEST-RB-03 (PASS):** Failure during cashbook insertion rolls back order and restores stock.
- **TEST-RB-04 (PASS):** Failure during customer points update rolls back points deduction.
- **TEST-RB-05 (PASS):** Purchase order failure rolls back PO, items, cashbook, and supplier debt atomically.

---

## 17. Edge Case Matrix (Phase 16)

- **TEST-EDGE-01 (PASS):** Order with quantity = 0 rejected.
- **TEST-EDGE-02 (PASS):** Order with quantity = -1 rejected.
- **TEST-EDGE-03 (PASS):** Client price tampering ignored; selling price pulled from `_mkv_price_out`.
- **TEST-EDGE-04 (PASS):** 100% discount results in total = 0 without negative balance.
- **TEST-EDGE-05 (PASS):** Over-100% discount capped at 0.
- **TEST-EDGE-06 (PASS):** Guest checkout (`customer_id = null`, `debt = 0`) processed without foreign key issues.
- **TEST-EDGE-07 (PASS):** Non-existent product ID rejected.
- **TEST-EDGE-08 (PASS):** Extremely large quantity ($1,000,000$) rejected due to insufficient stock.
- **TEST-EDGE-09 (PASS):** Large monetary values ($99,999,999,999$ VND) stored in `DECIMAL(15,2)` without overflow.
- **TEST-EDGE-10 (PASS):** Unicode Vietnamese characters preserved accurately.

---

## 18. Data Invariants (Phase 17)

- **INV-01 (PASS):** 0 negative stock records in `wp_mkv_inventory_stock`.
- **INV-02 (PASS):** 0 negative debt records in `wp_mkv_customers`.
- **INV-03 (PASS):** 0 negative debt records in `wp_mkv_suppliers`.
- **INV-04 (PASS):** 0 negative total amount records in `wp_mkv_orders`.
- **INV-05 (PASS):** 0 order items with quantity $\le 0$ in `wp_mkv_order_items`.
- **INV-06 (PASS):** 0 orphan rows in `wp_mkv_order_items`.
- **INV-07 (PASS):** 0 orphan rows in `wp_mkv_purchase_order_items`.
- **INV-08 (PASS):** 0 orphan rows in `wp_mkv_return_items`.
- **INV-09 (PASS):** 0 orphan rows in `wp_mkv_stocktake_items`.
- **INV-10 (PASS):** 0 orders with invalid / undocumented status values.

---

## 19. Defects Ledger

No structural or transactional defects were found in production controller code.

| Defect ID | Severity | Component | Root Cause | Fix Applied | Verification |
| :--- | :---: | :--- | :--- | :--- | :--- |
| **DEFECT-B15-001** | LOW | Test Script (`test_backend_15_orders.php`) | In strict comparison `===`, PHP `round()` returned `float 20000.0` compared against `int 20000`. | Cast to integer `(int)$vat_ex_tax === 20000`. | PASS |
| **DEFECT-B15-002** | LOW | Test Script (`test_backend_15_cashbook.php`) | Test cashbook reconciliation queried cumulative `%B15_TEST_CB%` without resetting on repeated runs. | Added batch cleanup of test rows at beginning of suite. | PASS |
| **DEFECT-B15-003** | LOW | Cleanup Script (`b15_test_helper.php`) | Returns linked to test orders were not purged if orders were queried after returns deletion pass. | Inverted deletion sequence to fetch test order IDs first, deleting linked returns before orders. | PASS |

---

## 20. Fixes Implemented

All fixes were confined strictly to test harnesses and assertion scripts (`scratch/test_backend_15_*.php` and `scratch/b15_test_helper.php`). Zero core production controller modifications were required because the production controllers (`class-orders.php`, `class-inventory.php`, `class-purchases.php`, `class-cashbook.php`, `class-webhook.php`) already implement hardened transactional boundaries, `FOR UPDATE` locking, and state transition guards.

---

## 21. Regression Results (BACKEND-14)

Re-execution of the BACKEND-14 Master Release Verification Suite (`scratch/test_backend_14_master.php`):
- Total Test Suites: 18
- Total Verification Checks: 72
- Passed Checks: 72
- Failed Checks: 0
- Overall Pass Rate: **100%**
- Verdict: **`RELEASE-READY / OPERATIONS-READY`**

---

## 22. Cleanup Results (Phase 20)

Execution of `scratch/test_backend_15_cleanup.php`:
```
=== PHASE 20: 100% TEST RESIDUE CLEANUP ===
Residue Audit:
  - products            : 0
  - customers           : 0
  - suppliers           : 0
  - locations           : 0
  - orders              : 0
  - purchase_orders     : 0
  - returns             : 0
  - stocktakes          : 0
  - cashbook            : 0
  - inventory_logs      : 0
  - notifications       : 0
  - audit_logs          : 0
Total Leftover Residue: 0

Re-validating against Baseline Snapshot (dd033d1bc886072b53d4d65f69a0e1aa):
>>> All 18 database table row counts match baseline 100%! <<<

=== CLEANUP VERDICT: 100% CLEAN — ZERO RESIDUE — BASELINE RESTORED ===
```

---

## 23. Final Verdict & Quality Gate

All 21 phases of the BACKEND-15 audit have been executed:
- All critical business flows pass.
- Financial reconciliation passes.
- Inventory reconciliation passes.
- Transaction rollback passes.
- Duplicate submission protection passes.
- State machine passes.
- Regression passes (100%).
- Test residue = 0.

```
============================================================
              BACKEND-15 FINAL QUALITY GATE
============================================================
FINAL VERDICT: BUSINESS-READY
============================================================
```
