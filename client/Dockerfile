FROM node:18

WORKDIR /app

COPY package*.json ./
RUN npm install

COPY . .

# Build for production
RUN npm run build

EXPOSE 3000

CMD ["npm", "run", "start"]
