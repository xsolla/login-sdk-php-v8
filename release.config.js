module.exports = {
  "plugins": [
    "@semantic-release/commit-analyzer",
    "@semantic-release/release-notes-generator",
    "@semantic-release/changelog",
    "@semantic-release/gitlab",
    ["@semantic-release/git", {
      "assets": ["CHANGELOG.md", "package-lock.json", "package.json"],
      "message": "chore(release): ${nextRelease.version} [skip release]\n\n${nextRelease.notes}"
    }]
  ],
  "preset": "angular",
  "tagFormat": "${version}"
}