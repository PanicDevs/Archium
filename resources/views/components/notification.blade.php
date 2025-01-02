@props(['type' => 'info', 'maxNotifications' => 5])

<div 
    x-cloak
    x-data="notifications"
    @notify.window="addNotification($event.detail[0].message, $event.detail[0].type)"
    class="fixed right-4 top-4 z-50 flex flex-col gap-3 pointer-events-none"
>
    <template x-for="notification in [...notifications].reverse().slice(0, maxNotifications)" :key="notification.id">
        <div
            x-show="notification.show"
            :class="[
                classes[notification.type],
                notification.animation
            ]"
            class="flex w-96 items-center gap-3 rounded-lg p-4 shadow-lg pointer-events-auto"
            :data-notification-id="notification.id"
        >
            <div class="flex h-8 w-8 shrink-0 items-center justify-center">
                <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" x-html="icons[notification.type]">
                </svg>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium" x-text="notification.message"></p>
            </div>
            <button 
                @click="removeNotification(notification.id)" 
                class="shrink-0 rounded-lg p-1.5 hover:bg-white/10"
            >
                <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    </template>
</div>

<style>
@keyframes notification-enter {
    0% {
        opacity: 0;
        transform: translateY(-100%);
    }
    50% {
        opacity: 1;
        transform: translateY(8px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes notification-leave {
    0% {
        opacity: 1;
        transform: translateY(0);
    }
    25% {
        opacity: 1;
        transform: translateY(-8px);
    }
    100% {
        opacity: 0;
        transform: translateY(50px) scale(0.95);
    }
}

.notification-enter {
    animation: notification-enter 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}

.notification-leave {
    animation: notification-leave 0.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
}
</style>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('notifications', () => ({
        notifications: [],
        nextId: 1,
        maxNotifications: 5,

        init() {
            this.$watch('notifications', value => {
                value.forEach(notification => {
                    if (notification.show && !notification.timeoutId) {
                        notification.timeoutId = setTimeout(() => {
                            this.removeNotification(notification.id);
                        }, 8000);
                    }
                });
            });
        },

        addNotification(message, type = 'info') {
            // Remove oldest notification if we're at max capacity
            if (this.notifications.length >= this.maxNotifications) {
                const oldestNotification = this.notifications[0];
                if (oldestNotification.timeoutId) {
                    clearTimeout(oldestNotification.timeoutId);
                }
                // Add leave animation to oldest notification
                oldestNotification.animation = 'notification-leave';
                setTimeout(() => {
                    this.notifications = this.notifications.slice(1);
                }, 500);
            }

            const notification = {
                id: this.nextId++,
                message,
                type: type || 'info',
                show: true,
                timeoutId: null,
                animation: 'notification-enter'
            };

            this.notifications.push(notification);
        },

        removeNotification(id) {
            const notification = this.notifications.find(n => n.id === id);
            if (notification) {
                if (notification.timeoutId) {
                    clearTimeout(notification.timeoutId);
                }
                notification.animation = 'notification-leave';
                setTimeout(() => {
                    notification.show = false;
                    this.notifications = this.notifications.filter(n => n.id !== id);
                }, 500);
            }
        },

        classes: {
            'success': 'bg-success-500 dark:bg-success-600 text-white',
            'error': 'bg-danger-500 dark:bg-danger-600 text-white',
            'warning': 'bg-warning-500 dark:bg-warning-600 text-white',
            'info': 'bg-info-500 dark:bg-info-600 text-white'
        },

        icons: {
            'success': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'error': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
            'warning': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
            'info': '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
        }
    }))
})
</script> 