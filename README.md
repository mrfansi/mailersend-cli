# MailerSend CLI

<p align="center">
    <img title="MailerSend CLI" height="100" src="https://raw.githubusercontent.com/laravel-zero/docs/master/images/logo/laravel-zero-readme.png" alt="Laravel Zero Logo" />
</p>

A command-line interface built with Laravel Zero for interacting with the MailerSend API. This CLI tool provides an efficient way to manage your MailerSend resources including domains, email sending, senders, templates, and API tokens.

## Features

- Domain management (verification, DNS records, settings)
- Email sending with support for attachments, headers, and personalization
- Sender identity management
- Email template operations
- API token management
- Built on top of Laravel Zero framework

## Installation

### Prerequisites
- PHP >= 8.0
- Composer

### Steps

1. Clone the repository:
```bash
git clone <repository-url>
cd mailersend
```

2. Install dependencies:
```bash
composer install
```

3. Create environment file:
```bash
cp .env.example .env
```

4. Configure your MailerSend API key in the `.env` file:
```env
MAILERSEND_API_KEY=your_api_key_here
```

## Available Commands

### Domain Management
```bash
# List all domains
./mailersend domain:list

# Add a new domain
./mailersend domain:add example.com

# Get domain details
./mailersend domain:get example.com
```

### Email Operations
```bash
# Send an email
./mailersend email:send --from="sender@domain.com" --to="recipient@domain.com" --subject="Test" --text="Hello World"

# Send email with HTML content
./mailersend email:send --from="sender@domain.com" --to="recipient@domain.com" --subject="Test" --html="<h1>Hello World</h1>"
```

### Sender Management
```bash
# List all senders
./mailersend sender:list

# Add a new sender
./mailersend sender:add --email="sender@domain.com" --name="John Doe"
```

### Template Operations
```bash
# List all templates
./mailersend template:list

# Get template details
./mailersend template:get template_id
```

### Token Management
```bash
# List all tokens
./mailersend token:list

# Create a new token
./mailersend token:create --name="My Token" --scopes="email.send,domains.read"
```

## Environment Configuration

The following environment variables can be configured in your `.env` file:

```env
MAILERSEND_API_KEY=your_api_key_here
MAILERSEND_API_URL=https://api.mailersend.com/v1
```

## Contributing

Feel free to submit issues and pull requests.

## License

This project is open-source software licensed under the MIT license.
