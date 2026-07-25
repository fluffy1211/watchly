module.exports = {
  testEnvironment: 'jsdom',
  setupFilesAfterEnv: ['<rootDir>/jest.setup.cjs'],
  moduleNameMapper: {
    '\\.module\\.css$': 'identity-obj-proxy',
    '\\.css$': '<rootDir>/jest.styleMock.cjs',
  },
  testPathIgnorePatterns: ['/node_modules/', '/dist/'],
}
