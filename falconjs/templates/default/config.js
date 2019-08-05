const dotenv = require('dotenv');
const routes = require('./routes');

// Import variables from local .env file.
dotenv.config();

module.exports = {
  BACKEND_URL: process.env.BACKEND_URL,
  FRONTEND_URL: process.env.FRONTEND_URL,
  CONSUMER_ID: process.env.FRONTEND_URL,
  ENVIRONMENT: process.env.ENVIRONMENT,
  PAYMENT_SECRET_HEADER_NAME: process.env.ENVIRONMENT,
  HTTP_AUTH_USER: process.env.HTTP_AUTH_USER,
  HTTP_AUTH_PASS: process.env.HTTP_AUTH_PASS,
  APP_ONLY_ROUTES: routes,
  FAVICON: '/asdasd',
};
