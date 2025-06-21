# Kev-Network

**Kev-Network** is a self-hosted platform that acts as a personal network landing page and monitoring hub.  
From here, users can access and manage services like Jellyfin, ISPConfig, and others via custom subdomains.

## Features

### Central Landing Page – [`kev-network.de`](https://kev-network.de)
- Entry point for all services (e.g. Jellyfin, ISPConfig, Webmail)
- Clean, mobile-first interface with dark theme
- Animated branding and modular layout via Twig

### Monitoring Dashboard – [`status.kev-network.de`](https://status.kev-network.de)
- **DynDNS Monitoring**: Logs and verifies dynamic IP updates
- **Jellyfin Integration**:
  - System status via API (version, updates, media stats)
  - Series & Movie integrity checks (missing sources, playback errors)
- Modular route/controller structure (Slim PHP Framework)
- Publicly visible, privacy-respecting statistics

## Built With

- PHP 8 (Slim Framework)
- Twig for templating
- Guzzle for Jellyfin API access
- Chart.js for uptime visualization
- Custom `.env`-based configuration (secure for GitHub usage)

## Privacy & Security

Kev-Network is built with privacy in mind:
- No user tracking
- Only non-sensitive, publicly-safe system info is exposed
- Real file paths are secured via `.env` media path whitelisting

## Project Structure
- /public/ → Public assets (CSS, JS, logos)
- /src/Routes/ → Slim route controllers (e.g. Jellyfin, DynDNS)
- /templates/ → Twig templates
 
## Live Demo

- **Landingpage**: [https://kev-network.de](https://kev-network.de)
- **Monitoring**: [https://status.kev-network.de](https://status.kev-network.de)

## License

This project is licensed under the [MIT License](LICENSE).


