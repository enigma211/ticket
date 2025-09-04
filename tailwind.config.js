import forms from '@tailwindcss/forms'
import typography from '@tailwindcss/typography'
import tailwindcssRtl from 'tailwindcss-rtl'

export default {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Vazirmatn', 'ui-sans-serif', 'system-ui'],
      },
      colors: {
        primary: { DEFAULT: '#2563eb' },
      },
    },
  },
  plugins: [forms, typography, tailwindcssRtl],
}
