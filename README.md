# RapidCare ambulance coordination demo

This repository contains a rebuilt, runnable ambulance booking and dispatch **portfolio demo** in [`app/`](app/). The original university PHP project remains in the repository for reference. RapidCare is not an operating emergency service and does not dispatch real ambulances. In Bangladesh, call **999** for emergencies.

## Run locally

Requires Node.js 24 or later. From `app/`:

```powershell
npm install
npm start
```

Open <http://localhost:3000>. The app creates its SQLite database in `app/data/` on first run. To create an admin account, run this in a second terminal after the server has started:

```powershell
$env:ADMIN_EMAIL='admin@example.com'
$env:ADMIN_PASSWORD='choose-a-strong-password'
npm run seed:admin
```

Configuration examples are in [`app/.env.example`](app/.env.example). The app does not automatically load `.env` files.

## Try the complete flow

1. Register a **driver** account, add a vehicle, and upload the driver licence and vehicle registration.
2. Sign in as **admin**, review the documents and approve the vehicle.
3. Sign in as the driver and mark the vehicle available.
4. Register a **requester** account and submit a request.
5. Sign in as admin and assign the available vehicle.
6. Sign in as driver, accept the trip, share a location if desired, and advance through the trip stages.
7. Sign in as requester and open **Track Request** in the navigation to view the status, location, and timeline.

If a driver cannot take an assigned request, they can decline it with a reason and the request returns to the dispatch queue. Dispatchers can reassign an active request to another available ambulance with a recorded reason. Requester and admin cancellations also require a reason; these operational notes appear in the request timeline.

Before approval, the driver must upload a **driver licence** and **vehicle registration** as PDF, PNG, or JPEG files of no more than 5 MB each. These files are stored outside the public web directory and can be downloaded only by their driver or an administrator. Replacing a document revokes vehicle approval until an administrator reviews it again.

The contact form stores messages for admin review. Public pages include Home, Services, Search, About, and Contact. Search filters available ambulances by location or equipment and vehicle type; each approved vehicle has a details page with its service area, crew, capacity, and equipment. Signed-in users have an **Updates** page for request events and a **Security** page to change their password. The request board checks for changes every 15 seconds while open.

The sign-in screen includes password recovery. Reset links expire after 30 minutes, work once, and revoke all existing sessions after use. In local development the reset link appears on screen so the flow can be tested. In production the API does not expose the link; connect an email provider before deployment to deliver it securely.

Use the **বাংলা / EN** control in the main navigation to switch the core interface between Bangla and English. The preference is saved in the browser. User-entered content such as names, addresses, vehicle descriptions, and timeline reasons remains in the language in which it was entered.

## Check the workflow

With the app running and the admin account configured in the terminal:

```powershell
$env:ADMIN_EMAIL='admin@example.com'
$env:ADMIN_PASSWORD='choose-a-strong-password'
npm run test:smoke
npm run test:workflow
npm run test:e2e
```

The browser suite uses installed Chrome at desktop and Pixel 7 viewport sizes. It checks public navigation, responsive overflow, requester registration and tracking, keyboard skip navigation, browser errors, and automated WCAG A/AA rules. Browser tests run against a separate temporary SQLite database under `test-results/`, leaving the local demo database clean.

## Current scope

**Where tracking is:** Select **Track Request** in the main navigation after signing in. The page lists your requests, shows the current dispatch stage and activity timeline, and displays the driver's shared location on an updating map. The driver starts **live sharing** from an accepted trip on the dashboard or tracking page. Browser geolocation generally requires localhost or HTTPS; sharing stops when the driver leaves the page or the trip ends. The map is unavailable until an assigned driver accepts and shares a location. This release does not include SMS, payments, production email delivery, independent provider identity verification, automated dispatch, or background GPS after the browser is closed. These require external services and operational processes before a real launch.

## Security and deployment notes

The app uses password hashing, HTTP only session cookies, origin checks for writes, role checks, basic account attempt limiting, escaped rendering, and parameterized SQLite statements. Password changes revoke all sessions. Deploy behind HTTPS, back up the database, add persistent or shared rate limiting and password recovery, and conduct a security review before inviting real users. Do not publish any personal or trip data from `app/data/`.
