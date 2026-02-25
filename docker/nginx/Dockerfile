# =============================================================================
# NGINX DOCKERFILE - Development Mode
# =============================================================================
# 
# WHAT THIS FILE DOES:
# This Dockerfile creates a lightweight Nginx container that serves as the
# web server for our Laravel application. Nginx handles HTTP requests and
# forwards PHP requests to the PHP-FPM container.
#
# WHY SEPARATE CONTAINER:
# - Nginx and PHP-FPM can scale independently
# - Smaller attack surface (security)
# - Industry standard for production
# - Easier debugging (separate logs)
#
# ALPINE LINUX:
# We use Alpine Linux because it's tiny (~5MB) and secure.
# It uses musl libc instead of glibc, making it smaller but still compatible.
# =============================================================================

# -----------------------------------------------------------------------------
# BASE IMAGE
# -----------------------------------------------------------------------------
# FROM: Specifies the base image to build from
# nginx:alpine = Official Nginx image built on Alpine Linux
# Alpine = Minimal Linux distro (~5MB), perfect for containers
# -----------------------------------------------------------------------------
FROM nginx:1.25-alpine

# -----------------------------------------------------------------------------
# LABELS (Metadata)
# -----------------------------------------------------------------------------
# Labels help organize and document your images
# These can be viewed with: docker inspect <image>
LABEL maintainer="Your Team"
LABEL description="Nginx reverse proxy for Laravel PHP-FPM"
LABEL version="1.0"

# -----------------------------------------------------------------------------
# INSTALL TOOLS (Optional but helpful for debugging)
# -----------------------------------------------------------------------------
# RUN: Executes commands during image build (not at container runtime)
# apk = Alpine Package Keeper (like apt for Ubuntu, brew for macOS)
# --no-cache = Don't cache package index (keeps image small)
# curl = For healthchecks and debugging
# tzdata = Timezone data (optional, for logging timestamps)
RUN apk add --no-cache curl tzdata

# -----------------------------------------------------------------------------
# COPY CONFIGURATION
# -----------------------------------------------------------------------------
# COPY: Copies files from host (build context) into the container
# Format: COPY <source-on-host> <destination-in-container>
#
# We copy our custom Nginx configuration into the container.
# /etc/nginx/conf.d/ is where Nginx looks for site configurations.
# Note: Path is relative to build context (project root)
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# -----------------------------------------------------------------------------
# CREATE LOG DIRECTORIES
# -----------------------------------------------------------------------------
# These directories will hold Nginx access and error logs.
# We create them explicitly to ensure proper permissions.
RUN mkdir -p /var/log/nginx

# -----------------------------------------------------------------------------
# EXPOSE PORT
# -----------------------------------------------------------------------------
# EXPOSE: Documents which port the container listens on
# This is DOCUMENTATION ONLY - it doesn't actually publish the port.
# The actual port mapping happens in docker-compose.yml
# Port 80 = Standard HTTP port
EXPOSE 80

# -----------------------------------------------------------------------------
# HEALTHCHECK
# -----------------------------------------------------------------------------
# HEALTHCHECK: Tells Docker how to test if the container is healthy
# This allows Docker to restart unhealthy containers automatically
#
# --interval=30s: Check every 30 seconds
# --timeout=3s: If no response in 3 seconds, consider it failed
# --start-period=5s: Wait 5 seconds after container starts before checking
# --retries=3: After 3 consecutive failures, mark as unhealthy
#
# The command: curl -f http://localhost/ returns exit code 0 on success
# -f = fail silently (return non-zero exit code on HTTP errors)
HEALTHCHECK --interval=30s --timeout=3s --start-period=5s --retries=3 \
    CMD curl -f http://localhost/ || exit 1

# -----------------------------------------------------------------------------
# DEFAULT COMMAND
# -----------------------------------------------------------------------------
# CMD: The command that runs when the container starts
# "nginx -g 'daemon off;'" runs Nginx in the foreground
# -g = set global directive
# daemon off = don't run as background process (required for Docker)
#
# Why daemon off? Docker needs the main process to stay running.
# If Nginx runs as a daemon (background), the container would exit immediately.
CMD ["nginx", "-g", "daemon off;"]

# =============================================================================
# DEVELOPMENT VS PRODUCTION CHANGES:
# =============================================================================
# 
# FOR PRODUCTION, YOU WOULD ADD:
#
# 1. SSL/TLS Configuration:
#    COPY ssl/ /etc/nginx/ssl/
#    Configure HTTPS in default.conf
#
# 2. Security Headers (in default.conf):
#    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
#    add_header Content-Security-Policy "default-src 'self';"
#    add_header X-Frame-Options "DENY" always;
#
# 3. Rate Limiting (in default.conf):
#    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
#
# 4. Gzip Compression (already in default.conf, but tune for production)
#
# 5. Caching Headers for static assets
#
# 6. Remove shell access for security:
#    RUN apk del curl (if not needed for healthcheck)
# =============================================================================