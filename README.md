# Invoice Parser

A Laravel application that uses Claude AI to automatically extract structured data from invoice images and PDFs. Upload an invoice, and the system will parse it into organized data including line items, totals, discounts, and party information.

## Features

- **AI-Powered Extraction**: Uses Claude 3.5 Sonnet to intelligently parse invoice documents
- **Multiple File Formats**: Supports JPG, PNG, and PDF uploads (up to 10MB)
- **Structured Data Output**: Extracts issuer/customer details, line items, discounts, other charges, and totals
- **User Authentication**: Each user manages their own invoices securely
- **Document Preview**: View the original uploaded document alongside extracted data
- **Totals Verification**: Automatically calculates and verifies totals against line items
- **Responsive UI**: Clean, modern interface built with Tailwind CSS

## Requirements

- PHP 8.2 or higher
- Composer
- Node.js & NPM
- SQLite (default) or MySQL/PostgreSQL
- Anthropic API key

## Installation

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/krl-invoices.git
cd krl-invoices
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Environment Setup

Copy the example environment file:

```bash
cp .env.example .env
```

Generate an application key:

```bash
php artisan key:generate
```

### 4. Configure Anthropic API

Add your Anthropic API key to the `.env` file:

```env
ANTHROPIC_API_KEY=your-api-key-here
CLAUDE_MODEL=claude-3-5-sonnet-latest
```

You can obtain an API key from [Anthropic's Console](https://console.anthropic.com/).

### 5. Database Setup

The application uses SQLite by default. Create the database and run migrations:

```bash
touch database/database.sqlite
php artisan migrate
```

For MySQL or PostgreSQL, update the `DB_*` variables in `.env` accordingly.

### 6. Storage Link

Create a symbolic link for file storage:

```bash
php artisan storage:link
```

### 7. Build Assets

```bash
npm run build
```

### 8. Start the Server

```bash
php artisan serve
```

Visit `http://localhost:8000` in your browser.

## Usage

### 1. Create an Account

Register a new user account at `/register` or log in at `/login`.

### 2. Upload an Invoice

1. Navigate to **Invoices** in the navigation menu
2. Click **Upload Invoice**
3. Select a JPG, PNG, or PDF file (max 10MB)
4. Click **Parse Invoice**

### 3. View Extracted Data

After processing, you'll see the extracted information including:

- **Invoice Information**: Number, date, currency
- **Issuer Details**: Name, VAT number, address
- **Customer Details**: Name, VAT number, address
- **Line Items**: Description, quantity, unit price, VAT rate, line total
- **Discounts**: Description and amount
- **Other Charges**: Shipping, handling, service fees
- **Totals**: Subtotal, VAT total, grand total

### 4. Manage Invoices

- View all your invoices in a paginated list
- Click on any invoice to see full details
- Delete invoices you no longer need

## Project Structure

```
krl-invoices/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── InvoiceController.php    # Main invoice operations
│   ├── Models/
│   │   ├── Invoice.php                  # Invoice model with relationships
│   │   ├── InvoiceLineItem.php          # Line item model
│   │   ├── InvoiceDiscount.php          # Discount model
│   │   └── InvoiceOtherCharge.php       # Other charges model
│   └── Services/
│       └── ClaudeInvoiceExtractor.php   # Claude AI integration service
├── config/
│   └── services.php                     # Anthropic configuration
├── database/
│   └── migrations/
│       ├── 2024_01_01_000001_create_invoices_table.php
│       ├── 2024_01_01_000002_create_invoice_line_items_table.php
│       ├── 2024_01_01_000003_create_invoice_discounts_table.php
│       └── 2024_01_01_000004_create_invoice_other_charges_table.php
├── resources/
│   └── views/
│       └── invoices/
│           ├── index.blade.php          # Invoice list view
│           ├── create.blade.php         # Upload form view
│           └── show.blade.php           # Invoice detail view
└── routes/
    └── web.php                          # Application routes
```

## Database Schema

### invoices

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| user_id | bigint | Foreign key to users |
| invoice_number | string | Invoice identifier |
| invoice_date | date | Invoice date |
| issuer_name | string | Issuer company name |
| issuer_vat | string | Issuer VAT number |
| issuer_address | text | Issuer address |
| customer_name | string | Customer name |
| customer_vat | string | Customer VAT number |
| customer_address | text | Customer address |
| currency | string(10) | Currency code (USD, EUR, etc.) |
| subtotal | decimal(15,2) | Subtotal amount |
| vat_total | decimal(15,2) | Total VAT amount |
| grand_total | decimal(15,2) | Grand total |
| file_path | string | Path to stored file |
| original_filename | string | Original uploaded filename |
| raw_response | text | Raw Claude API response |
| created_at | timestamp | Creation timestamp |
| updated_at | timestamp | Update timestamp |

### invoice_line_items

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| invoice_id | bigint | Foreign key to invoices |
| description | text | Item description |
| quantity | decimal(15,4) | Quantity |
| unit_price | decimal(15,2) | Price per unit |
| vat_rate | decimal(5,2) | VAT percentage |
| line_total | decimal(15,2) | Total for this line |

### invoice_discounts

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| invoice_id | bigint | Foreign key to invoices |
| description | string | Discount description |
| amount | decimal(15,2) | Discount amount |

### invoice_other_charges

| Column | Type | Description |
|--------|------|-------------|
| id | bigint | Primary key |
| invoice_id | bigint | Foreign key to invoices |
| description | string | Charge description |
| amount | decimal(15,2) | Charge amount |

## Claude AI Integration

The `ClaudeInvoiceExtractor` service handles all communication with the Anthropic API.

### Extracted Data Schema

The service requests Claude to return data in this JSON format:

```json
{
  "issuer": {
    "name": "Company Name",
    "vat_number": "VAT123456",
    "address": "123 Main St, City"
  },
  "customer": {
    "name": "Customer Name",
    "vat_number": "VAT789012",
    "address": "456 Oak Ave, Town"
  },
  "invoice_number": "INV-001",
  "invoice_date": "2024-01-15",
  "currency": "USD",
  "line_items": [
    {
      "description": "Product or Service",
      "quantity": 2,
      "unit_price": 100.00,
      "vat_rate": 20,
      "line_total": 200.00
    }
  ],
  "discounts": [
    {
      "description": "Early payment discount",
      "amount": 10.00
    }
  ],
  "other_charges": [
    {
      "description": "Shipping",
      "amount": 15.00
    }
  ],
  "totals": {
    "subtotal": 200.00,
    "vat_total": 40.00,
    "grand_total": 245.00
  }
}
```

### Supported Models

You can configure the Claude model in your `.env` file:

```env
CLAUDE_MODEL=claude-3-5-sonnet-latest
```

Other options include:
- `claude-3-opus-latest` - Most capable, slower
- `claude-3-haiku-latest` - Fastest, most economical

## Configuration

### Environment Variables

| Variable | Description | Default |
|----------|-------------|---------|
| `ANTHROPIC_API_KEY` | Your Anthropic API key | (required) |
| `CLAUDE_MODEL` | Claude model to use | `claude-3-5-sonnet-latest` |
| `FILESYSTEM_DISK` | Storage disk for uploads | `local` |

### File Upload Limits

Default limits are set in `InvoiceController.php`:
- Maximum file size: 10MB
- Allowed types: JPG, JPEG, PNG, PDF

To change these, modify the validation rules in the `store` method:

```php
$request->validate([
    'invoice_file' => [
        'required',
        'file',
        'max:20480', // 20MB
        'mimes:jpg,jpeg,png,pdf,tiff',
    ],
]);
```

## API Costs

Claude API usage is billed based on input and output tokens. Invoice parsing typically uses:
- **Input**: ~1,000-5,000 tokens (image + prompt)
- **Output**: ~500-2,000 tokens (JSON response)

Refer to [Anthropic's pricing](https://www.anthropic.com/pricing) for current rates.

## Troubleshooting

### "Anthropic API key is not configured"

Ensure `ANTHROPIC_API_KEY` is set in your `.env` file and run:
```bash
php artisan config:clear
```

### "Failed to parse invoice data"

This usually means Claude's response wasn't valid JSON. Check the `raw_response` field in the database for debugging. Common causes:
- Poor image quality
- Handwritten invoices
- Unusual invoice formats

### File upload errors

- Ensure storage link exists: `php artisan storage:link`
- Check file permissions on `storage/app/public`
- Verify file size limits in `php.ini` (`upload_max_filesize`, `post_max_size`)

### Images not displaying

- Run `php artisan storage:link`
- Ensure files are in `storage/app/public/invoices`
- Check that `FILESYSTEM_DISK=local` or configure accordingly

## Development

### Running Tests

```bash
php artisan test
```

### Code Style

This project follows PSR-12 coding standards. Run Laravel Pint for formatting:

```bash
./vendor/bin/pint
```

### Adding New Extracted Fields

1. Add migration for new database columns
2. Update the `Invoice` model's `$fillable` array
3. Modify the prompt in `ClaudeInvoiceExtractor::buildPrompt()`
4. Update the `store` method in `InvoiceController`
5. Add the field to the show view

## Security Considerations

- All invoice routes require authentication
- Users can only access their own invoices
- Uploaded files are stored outside the web root
- File types are validated server-side
- CSRF protection on all forms

## Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## Acknowledgments

- [Laravel](https://laravel.com/) - The PHP framework
- [Anthropic](https://www.anthropic.com/) - Claude AI
- [Tailwind CSS](https://tailwindcss.com/) - Styling
- [Laravel Breeze](https://laravel.com/docs/starter-kits#laravel-breeze) - Authentication scaffolding
