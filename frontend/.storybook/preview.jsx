import '../src/styles/global.css'

/** @type { import('@storybook/react-vite').Preview } */
const preview = {
  parameters: {
    backgrounds: {
      default: 'watchly-dark',
      values: [
        { name: 'watchly-dark', value: '#0D0D0F' },
      ],
    },
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/i,
      },
    },
  },
  // Every component is built for the app's dark palette (--watchly-color-text,
  // --watchly-color-border, etc. are near-white/near-black). The `backgrounds`
  // parameter above only themes the Canvas tab, not the Docs page's white
  // preview boxes, so wrap every story in the real background explicitly.
  decorators: [
    (Story) => (
      <div style={{ background: 'var(--watchly-color-bg)', padding: 'var(--watchly-space-6)' }}>
        <Story />
      </div>
    ),
  ],
};

export default preview;
