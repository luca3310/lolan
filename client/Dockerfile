FROM node:18

WORKDIR /app

# Kopier package.json og package-lock.json først
COPY package*.json ./

# Installer dependencies
RUN npm install

# Kopier resten af filerne
COPY . .

EXPOSE 3000

# Start applikationen i development mode
CMD ["npm", "run", "dev", "--", "--hostname", "0.0.0.0"]
