# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Two audiences, weighted together:

- **The course instructor (Programación IV, 2º parcial)** evaluating functionality, completeness against the assignment, and polish.
- **The fictional Banco Andino's users**, designed for as if real:
  - **Visitors**: read bank info, register as clients, send contact inquiries.
  - **Clients**: view accounts and movements (with search), transfer money, edit their email, change password.
  - **Operators**: register clients, search clients, record deposits and withdrawals.
  - **Administrators**: summary panel plus CRUD for accounts, movements, users and inquiries.

## Product Purpose

A bank website built as the integrating project for Programación IV. Success means every assignment requirement works and is easy for the evaluator to verify, while the site feels like a credible, usable bank for each role.

## Operating Context

- Runs locally on XAMPP (PHP 8+, MySQL/MariaDB) and online on Railway (FrankenPHP + MySQL service).
- Login chooses access type: *Clientes* or *Empleados*; role mismatch is rejected.
- Test users and the shared password `123456` are documented in README.md; evaluators will use them.
- Currencies in use: ARS and USD; account types include caja de ahorro and cuenta corriente.

## Capabilities and Constraints

- **Hard constraint:** the assignment's requirements (consigna del parcial) must never break — e.g. JS form validations (`js/validaciones.js`), responsive behavior, role-based access, the documented flows.
- Current stack: plain PHP + HTML/CSS/JavaScript, no frameworks; single stylesheet `css/estilos.css`, shared `includes/header.php` / `footer.php`. Links are relative via the `RAIZ` constant so the site works in any subfolder.
- `mail()` doesn't send on Railway or bare XAMPP; inquiries are always stored in the `contactos` table.
- UI language: Spanish (rioplatense).

## Brand Commitments

- Name: **Banco Andino**.
- Voice: close and modern — a friendly digital bank, rioplatense Spanish with *vos*/tuteo, clear and warm rather than stiff.

## Evidence on Hand

- Seed data in `sql/banco.sql` (test clients, accounts, movements) and `sql/diagrama_relaciones.svg`.
- No real customers, testimonials, rates, or legal/regulatory claims exist; do not fabricate them.

## Product Principles

1. The assignment comes first: no design change may remove or obscure a required feature.
2. Every role should find its main task immediately; the evaluator should never hunt for a feature.
3. Credible as a bank: money, balances and movements are shown precisely and unambiguously (currency always explicit).
4. Friendly, modern tone without sacrificing the trust a bank needs.
5. Keep it plain-PHP-simple: changes stay within the existing structure and stylesheet.
