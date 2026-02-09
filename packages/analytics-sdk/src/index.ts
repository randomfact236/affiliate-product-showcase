export const initAnalytics = (apiKey: string) => {
    console.log('Analytics initialized with key:', apiKey);
    return {
        track: (event: string, properties?: Record<string, any>) => {
            console.log('Tracking event:', event, properties);
        }
    };
};
