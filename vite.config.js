const vuetify = createVuetify({
    components,
    directives,
    theme: {
        defaultTheme: 'dark',
        themes: {
            dark: {
                colors: {
                    primary: '#1867C0',
                    secondary: '#5CBBF6',
                    accent: '#82B1FF',
                    error: '#FF5252',
                    info: '#2196F3',
                    success: '#4CAF50',
                    warning: '#FFC107',
                    background: '#121212',
                    surface: '#1E1E1E',
                }
            }
        },
        variations: {
            colors: ['primary', 'secondary'],
            lighten: 5,
            darken: 5,
        }
    },
    defaults: {
        VCard: {
            elevation: 1,
        },
        VBtn: {
            variant: 'flat',
        },
        VTextField: {
            variant: 'outlined',
        }
    }
})