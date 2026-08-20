/** @type { import('@storybook/react-vite').StorybookConfig } */
const config = {
  "stories": [
    "../src/**/*.mdx",
    "../src/**/*.stories.@(js|jsx)"
  ],
  "addons": [
    "@storybook/addon-docs"
  ],
  "framework": "@storybook/react-vite"
};
export default config;
