import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

export default {
  content: [
    './storage/framework/views/*.php',
    './resources/js/**/*.vue',
    './resources/js/**/*.ts',
    './resources/views/**/*.blade.php',
  ],

  theme: {
    extend: {
      colors: {
        primary: {
          DEFAULT: '#80CCFC',
          light: '#addaf9',
          lightest: '#e7f4fe',
          alt: '#057DC7',
          dark: '#29719f',
          darkest: '#237cbd',
          other: '#186ba3',
          shopping: '#f4f9fd',
        },

        secondary: {
          light: '#ecd14a',
          DEFAULT: '#DBBC25',
        },

        grey: {
          DEFAULT: '#666',
          light: '#f7f7f7',
          lightest: '#fbfbfb',
          dark: '#787878',
          darker: '#595959',
          darkest: '#222',
          off: '#ccc',
          'off-light': '#e8e8e8',
          'off-dark': '#bbb',
        },

        red: {
          DEFAULT: '#f00',
          light: '#ff6060',
          dark: '#E53E3E',
        },

        green: {
          DEFAULT: '#00e800',
          dark: '#17a417',
        },

        rss: '#FF6600',
      },

      containers: {
        xxs: '400px',
        xs: '500px',
        xmd: '860px',
      },

      fontFamily: {
        sans: ['Raleway', 'ui-sans-serif'],
        coeliac: ['Hangyaboly', 'ui-sans-serif'],
      },

      maxWidth: {
        '1/2': '50%',
        '1/3': '33%',
        16: '16rem',
        18: '18rem',
        '8xl': '88rem',
      },

      minHeight: {
        map: '300px',
        'map-small': '200px',
      },

      minWidth: {
        '1/4': '25%',
      },

      screens: {
        xxs: '400px',
        xs: '500px',
        xmd: '860px',
      },

      spacing: {
        '1.75': '11px',
      },

      typography: ({ theme }: { theme: (prop: string) => string }) => ({
        DEFAULT: {
          css: {
            a: {
              color: theme('colors.primary.darkest'),
              fontWeight: theme('fontWeight.semibold'),
              textDecoration: 'none',
              transition: theme('transition'),
              '&:hover': {
                color: theme('colors.grey.dark'),
              },
            },
            blockquote: {
              // @apply bg-blue-light bg-opacity-50 p-2 rounded shadow text-sm;
              backgroundColor: theme('colors.primary.lightest'),
              padding: theme('padding.2'),
              borderRadius: theme('borderRadius.DEFAULT'),
              boxShadow: theme('boxShadow.DEFAULT'),
              fontSize: theme('text.sm'),
              borderInlineColor: theme('colors.secondary.DEFAULT'),
              borderInlineWidth: '0.5rem',
              borderRight: '0',
            },
            ol: {
              listStyle: 'auto',
            },
            table: {
              tableLayout: 'fixed',
              tr: {
                borderBottomColor: theme('colors.primary.light'),
                borderBottomWidth: '2px',
                '&:last-of-type': {
                  borderBottomWidth: '0',
                },
                'th, td': {
                  padding: '4px',
                  borderRightColor: theme('colors.primary.light'),
                  borderRightWidth: '2px',
                  '&:last-child': {
                    borderRightWidth: '0',
                  },
                },
              },
            },
          },
        },
      }),
    },
  },

  plugins: [forms, typography],
};
