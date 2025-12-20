#!/bin/bash
# Discord Bot - Production Deployment Script
# Usage: bash deploy.sh

echo "🚀 Starting Discord Bot Deployment..."
echo ""

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if we're in the right directory
if [ ! -f "artisan" ]; then
    echo -e "${RED}❌ Error: artisan file not found. Please run this script from the project root.${NC}"
    exit 1
fi

echo -e "${YELLOW}Step 1: Clearing caches...${NC}"
php artisan config:clear
php artisan route:clear
php artisan cache:clear
php artisan view:clear
echo -e "${GREEN}✅ Caches cleared${NC}"
echo ""

echo -e "${YELLOW}Step 2: Rebuilding caches...${NC}"
php artisan config:cache
php artisan route:cache
echo -e "${GREEN}✅ Caches rebuilt${NC}"
echo ""

echo -e "${YELLOW}Step 3: Setting permissions...${NC}"
chmod -R 775 storage bootstrap/cache
echo -e "${GREEN}✅ Permissions set${NC}"
echo ""

echo -e "${YELLOW}Step 4: Verifying configuration...${NC}"
echo "Discord Public Key:"
php artisan tinker --execute="echo config('discord.public_key') ? '✅ Configured' : '❌ Not configured';"
echo ""

echo -e "${YELLOW}Step 5: Verifying routes...${NC}"
php artisan route:list --path=discord
echo ""

echo -e "${YELLOW}Step 6: Testing endpoint...${NC}"
ENDPOINT="https://bot-dc-news.koys.my.id/api/discord/interactions"
RESPONSE=$(curl -s -X GET "$ENDPOINT")
if [ "$RESPONSE" == '{"status":"ok"}' ]; then
    echo -e "${GREEN}✅ Endpoint responding correctly${NC}"
else
    echo -e "${RED}❌ Unexpected response: $RESPONSE${NC}"
fi
echo ""

echo -e "${YELLOW}Step 7: Checking logs...${NC}"
if [ -f "storage/logs/laravel.log" ]; then
    echo "Last 5 log entries:"
    tail -5 storage/logs/laravel.log
    echo -e "${GREEN}✅ Logs accessible${NC}"
else
    echo -e "${RED}❌ No log file found${NC}"
fi
echo ""

echo -e "${GREEN}🎉 Deployment complete!${NC}"
echo ""
echo "📋 Next steps:"
echo "1. Go to Discord Developer Portal"
echo "2. Set Interactions Endpoint URL: $ENDPOINT"
echo "3. Watch logs: tail -f storage/logs/laravel.log"
echo "4. Test slash commands in Discord"
echo ""
