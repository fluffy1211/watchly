/** @type { import('@storybook/react-vite').StorybookConfig } */
const config = {
  "stories": [
    "../src/storybook/**/*.mdx",
    "../src/storybook/**/*.stories.@(js|jsx)"
  ],
  "addons": [
    "@storybook/addon-docs"
  ],
  "framework": "@storybook/react-vite"
};
export default config;
